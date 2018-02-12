<?php //strict

namespace IO\Services;

use IO\Constants\SessionStorageKeys;
use IO\Constants\LogLevel;
use IO\Helper\RuntimeTracker;

/**
 * Class BasketService
 * @package IO\Services
 */
class NotificationService
{
    use RuntimeTracker;

    /**
     * @var SessionStorageService
     */
    private $sessionStorageService;

    /**
     * BasketService constructor.
     * @param \IO\Services\SessionStorageService $sessionStorageService
     */
    public function __construct(SessionStorageService $sessionStorageService)
    {
        $this->start("constructor");
        $this->sessionStorageService = $sessionStorageService;
        $this->track("constructor");
    }

    /**
     * @param bool $clear
     * @return array
     */
    public function getNotifications($clear = true):array
    {
        $this->start("getNotifications");
        $notifications = json_decode($this->sessionStorageService->getSessionValue(SessionStorageKeys::NOTIFICATIONS));

        if ($notifications == null || !is_array($notifications))
        {
            $notifications = array();
        }

        if ($clear)
        {
            $this->sessionStorageService->setSessionValue(SessionStorageKeys::NOTIFICATIONS, json_encode(array()));
        }

        $this->track("getNotifications");

        return $notifications;
    }

    /**
     * @param string $message
     * @param string $type
     * @param int $code
     */
    private function addNotification(string $message, string $type, int $code = 0)
    {
        $this->start("addNotification");
        $notifications = $this->getNotifications(false);

        array_push($notifications, array(
            'message' => $message,
            'type' => $type,
            'code' => $code
        ));

        $this->sessionStorageService->setSessionValue(SessionStorageKeys::NOTIFICATIONS, json_encode($notifications));
        $this->track("addNotification");
    }

    /**
     * @param string $message
     */
    public function log(string $message)
    {
        $this->start("log");
        $this->addNotification($message, LogLevel::LOG);
        $this->track("log");
    }

    /**
     * @param string $message
     */
    public function info(string $message)
    {
        $this->start("info");
        $this->addNotification($message, LogLevel::INFO);
        $this->track("info");
    }

    /**
     * @param string $message
     */
    public function warn(string $message)
    {
        $this->start("warn");
        $this->addNotification($message, LogLevel::WARN);
        $this->track("warn");
    }

    /**
     * @param string $message
     */
    public function error(string $message)
    {
        $this->start("error");
        $this->addNotification($message, LogLevel::ERROR);
        $this->track("error");
    }

    /**
     * @param string $message
     */
    public function success(string $message)
    {
        $this->start("success");
        $this->addNotification($message, LogLevel::SUCCESS);
        $this->track("success");
    }

    /**
     * @param $type
     * @param int $code
     */
    public function addNotificationCode($type, int $code = 0)
    {
        $this->start("addNotification");
        $this->addNotification("", $type, $code);
        $this->track("addNotification");
    }
}
