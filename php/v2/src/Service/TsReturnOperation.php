<?php

declare(strict_types=1);

namespace Php\V2\Service;

use Exception;
use Php\V2\Client\MessagesClient;
use Php\V2\Entity\Contractor;
use Php\V2\Entity\Employee;
use Php\V2\Entity\Seller;
use Php\V2\Enum\NotificationEvents;
use Php\V2\Enum\Status;
use Php\V2\Exception\NotificationException;
use Php\V2\Exception\OperationValidationException;
use Php\V2\Repository\EmailRepository;
use Php\V2\ValueObject\Operation;

class TsReturnOperation extends ReferencesOperation
{
    private const TS_GOODS_PERMIT = 'tsGoodsReturn';
    private const EXCEPTION_STATUS_BAD_REQUEST = 400;
    private const EXCEPTION_STATUS_INTERNAL_ERROR = 500;
    public const TYPE_NEW    = 1;
    public const TYPE_CHANGE = 2;

    // Код принимает запрос, содержащий данные о произошедшем событии, валидирует его,
    // и направняет письма с данной инфомацией сотрудникам и клиентам если произошла смена статуса.
    // Если известно, что используется мобильный клиент, также отправляется мобильное уведомление
    //
    // Были внесены следующие изменения в целях улучшения качества кода:
    // - внедрена строгая типизация (declare(strict_types=1))
    // - данные из запроса приводим к типам, функции принимают определенные типы
    // - улучшена валидация данных в запросе
    // - для лучшей читаемости работаем с объектами
    // - код в файле "others.php" разделен на компоненты-классы в разных файлах по PSR-4
    // - часть кода вынесена в отдельные функции по принципу разделения логики
    // - фикс code style, чистоты кода

    /**
     * @throws Exception
     */
    public function doOperation(): array
    {
        $result = [
            'notificationEmployeeByEmail' => false,
            'notificationClientByEmail'   => false,
            'notificationClientBySms'     => [
                'isSent'  => false,
                'message' => '',
            ],
        ];

        try {
            $operation = Operation::fromRequestData($this->getRequest('data'));
        } catch (OperationValidationException $e) {
            if ($e->getCode() === OperationValidationException::INVALID_RESELLER_ID_CODE) {
                $result['notificationClientBySms']['message'] = $e->getMessage();

                return $result;
            } else {
                throw new Exception($e->getMessage(), self::EXCEPTION_STATUS_BAD_REQUEST);
            }
        }

        $resellerId = $operation->getResellerId();
        $reseller = Seller::getById($resellerId);

        if ($reseller === null) {
            throw new Exception('Seller not found!', self::EXCEPTION_STATUS_BAD_REQUEST);
        }

        $client = Contractor::getById($operation->getClientId());

        if ($client->getType() !== Contractor::TYPE_CUSTOMER || $client->getResellerId() !== $resellerId) {
            throw new Exception('сlient not found!', self::EXCEPTION_STATUS_BAD_REQUEST);
        }

        $expertId = $operation->getExpertId();
        $creatorId = $operation->getCreatorId();
        $creator = Employee::getById($creatorId);

        if ($creator === null) {
            throw new Exception('Creator not found!', self::EXCEPTION_STATUS_BAD_REQUEST);
        }

        $expert = Employee::getById($expertId);

        if ($expert === null) {
            throw new Exception('Expert not found!', self::EXCEPTION_STATUS_BAD_REQUEST);
        }

        $notificationType = $operation->getNotificationType();
        $templateData = $this->createTemplateData($operation, $creator, $expert, $client);

        $this->sendEmployeeEmails($resellerId, $templateData);
        $differences = $operation->getDifferences();

        // Шлём клиентское уведомление, только если произошла смена статуса
        if ($notificationType === self::TYPE_CHANGE && $differences !== null) {
            if (!empty($emailFrom) && !empty($client->getEmail())) {
                MessagesClient::sendMessage(
                    [ // MessageTypes::EMAIL
                       'emailFrom' => $emailFrom,
                       'emailTo'   => $client->getEmail(),
                       'subject'   => $this->__('complaintClientEmailSubject', $templateData, $resellerId),
                       'message'   => $this->__('complaintClientEmailBody', $templateData, $resellerId),
                    ], $resellerId, NotificationEvents::CHANGE_RETURN_STATUS, $client->getId(), $differences->getTo()
                );
                $result['notificationClientByEmail'] = true;
            }

            if ($client->getClientType() === Contractor::CLIENT_MOBILE) {
                $res = null;

                try {
                    $res = NotificationManager::send(
                        $resellerId,
                        $client->getId(),
                        NotificationEvents::CHANGE_RETURN_STATUS,
                        $differences->getTo(),
                        $templateData,
                    );
                } catch (NotificationException $e) {
                    $result['notificationClientBySms']['message'] = $e->getErrorReason();
                }

                if ($res) {
                    $result['notificationClientBySms']['isSent'] = true;
                }
            }
        }

        return $result;
    }

    /**
     * @throws Exception
     */
    protected function createTemplateData(
        Operation $operation,
        Employee $creator,
        Employee $expert,
        Contractor $client
    ): array {
        $templateDifferences = '';
        $notificationType = $operation->getNotificationType();
        $differences = $operation->getDifferences();

        if ($notificationType === self::TYPE_NEW) {
            $templateDifferences = $this->__('NewPositionAdded', null, $operation->getResellerId());
        } elseif ($notificationType === self::TYPE_CHANGE && $differences != null) {
            $templateDifferences = $this->__(
                'PositionStatusHasChanged', [
                'FROM' => Status::byId($differences->getFrom()),
                'TO'   => Status::byId($differences->getTo()),
                ], $operation->getResellerId()
            );
        }

        $templateData = [
            'COMPLAINT_ID'       => $operation->getComplaintId(),
            'COMPLAINT_NUMBER'   => $operation->getComplaintNumber(),
            'CREATOR_ID'         => $operation->getCreatorId(),
            'CREATOR_NAME'       => $creator->getFullName(),
            'EXPERT_ID'          => $operation->getExpertId(),
            'EXPERT_NAME'        => $expert->getFullName(),
            'CLIENT_ID'          => $operation->getClientId(),
            'CLIENT_NAME'        => $client->getFullName(),
            'CONSUMPTION_ID'     => $operation->getConsumptionId(),
            'CONSUMPTION_NUMBER' => $operation->getConsumptionNumber(),
            'AGREEMENT_NUMBER'   => $operation->getAgreementNumber(),
            'DATE'               => $operation->getDate(),
            'DIFFERENCES'        => $templateDifferences,
        ];

        $this->validateTemplateData($templateData);

        return $templateData;
    }

    protected function sendEmployeeEmails(int $resellerId, array $templateData): void
    {
        $emailFrom = EmailRepository::getResellerEmailFrom($resellerId);
        // Получаем email сотрудников из настроек
        $emails = EmailRepository::getEmailsByPermit($resellerId, self::TS_GOODS_PERMIT);

        if (!empty($emailFrom) && count($emails) > 0) {
            foreach ($emails as $email) {
                MessagesClient::sendMessage(
                    [ // MessageTypes::EMAIL
                       'emailFrom' => $emailFrom,
                       'emailTo'   => $email,
                       'subject'   => $this->__('complaintEmployeeEmailSubject', $templateData, $resellerId),
                       'message'   => $this->__('complaintEmployeeEmailBody', $templateData, $resellerId),
                    ], $resellerId, NotificationEvents::CHANGE_RETURN_STATUS
                );
                $result['notificationEmployeeByEmail'] = true;
            }
        }
    }

    /**
     * @throws Exception
     */
    private function validateTemplateData(array $templateData): void
    {
        // Если хоть одна переменная для шаблона не задана, то не отправляем уведомления
        foreach ($templateData as $key => $tempData) {
            if (empty($tempData)) {
                throw new Exception("Template Data ({$key}) is empty!", self::EXCEPTION_STATUS_INTERNAL_ERROR);
            }
        }
    }

    //mock
    protected function __(string $message, ?array $context, int $id): string
    {
        return $message;
    }
}
