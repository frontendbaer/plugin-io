<?php

namespace IO\Services;

use IO\DBModels\PasswordReset;
use IO\Helper\RuntimeTracker;
use Plenty\Modules\Account\Contact\Contracts\ContactRepositoryContract;
use Plenty\Modules\Account\Contact\Models\Contact;
use Plenty\Plugin\Mail\Contracts\MailerContract;
use IO\Repositories\CustomerPasswordResetRepository;
use IO\Services\WebstoreConfigurationService;
use Plenty\Plugin\Application;
use Plenty\Plugin\Templates\Twig;
use Plenty\Modules\Authorization\Services\AuthHelper;

class CustomerPasswordResetService
{
    use RuntimeTracker;

    private $customerPasswordResetRepo;
    private $contactRepository;
    private $webstoreConfig;
    
    public function __construct(CustomerPasswordResetRepository $customerPasswordResetRepo, ContactRepositoryContract $contactRepository)
    {
        $this->start("constructor");
        $this->customerPasswordResetRepo = $customerPasswordResetRepo;
        $this->contactRepository = $contactRepository;
        $this->loadWebstoreConfig();
        $this->track("constructor");
    }
    
    public function resetPassword($email, $template)
    {
        $this->start("resetPassword");
        $contactId = $this->getContactIdbyEmailAddress($email);

        if((int)$contactId > 0)
        {
            $hash = $this->generateHash();
            $this->customerPasswordResetRepo->addEntry($contactId, $email, $hash);
            $resetURL = $this->buildMailURL($contactId, $hash);

            $contact = $this->getContactData($contactId);

            $mailContent = $resetURL;
            if(strlen($template) && $contact instanceof Contact)
            {
                $mailTemplateParams = [
                    'firstname' => $contact->firstName,
                    'lastname'  => $contact->lastName,
                    'email'     => $email,
                    'url'       => $resetURL,
                    'shopname'  => $this->webstoreConfig->name
                ];

                /**
                 * @var Twig
                 */
                $twig = pluginApp(Twig::class);
                $renderedMailTemplate = $twig->render($template, $mailTemplateParams);

                if(strlen($renderedMailTemplate))
                {
                    $mailContent = $renderedMailTemplate;
                }
            }

            /**
             * @var MailerContract $mailer
             */
            $mailer = pluginApp(MailerContract::class);
            $mailer->sendHtml($mailContent, $email, 'password reset');
        }

        $this->track("resetPassword");
        return true;
    }

    public function getContactIdbyEmailAddress($email)
    {
        $this->start("getContactIdByEmailAddress");
        $contactId = $this->contactRepository->getContactIdByEmail($email);
        $this->track("getContactIdByEmailAddress");

        return $contactId;
    }
    
    private function generateHash()
    {
        return sha1(microtime(true));
    }
    
    private function buildMailURL($contactId, $hash)
    {
        $this->start("buildMailURL");
        $domain = $this->webstoreConfig->domainSsl;
        $url = $domain.'/password-reset/'.$contactId.'/'.$hash;

        $this->track("buildMailURL");
        return $url;
    }
    
    public function checkHash($contactId, $hash)
    {
        $this->start("checkHash");
        $existingEntry = $this->customerPasswordResetRepo->findExistingEntry((int)pluginApp(Application::class)->getPlentyID(), (int)$contactId);
        if($existingEntry instanceof PasswordReset && $existingEntry->hash == $hash && $this->checkHashExpiration($existingEntry->timestamp))
        {
            $this->track("checkHash");
            return true;
        }

        $this->track("checkHash");
        return false;
    }
    
    public function checkHashExpiration($hashTimestamp)
    {
        $this->start("checkHashExpiration");
        $expirationDays = 1;
        $unixTimestamp = strtotime($hashTimestamp);
        if( ((int)$unixTimestamp > 0) && (time() > ($unixTimestamp + ((24*60*60)*$expirationDays))) )
        {
            $this->track("checkHashExpiration");
            return false;
        }

        $this->track("checkHashExpiration");
        return true;
    }
    
    public function findExistingHash($contactId)
    {
        $this->start("findExistingHash");
        $result = $this->customerPasswordResetRepo->findExistingEntry((int)pluginApp(Application::class)->getPlentyID(), $contactId);
        $this->start("findExistingHash");

        return $result;
    }
    
    public function deleteHash($contactId)
    {
        $this->start("deleteKey");
        $result = $this->customerPasswordResetRepo->deleteEntry((int)$contactId);
        $this->track("deleteKey");

        return $result;
    }
    
    private function loadWebstoreConfig()
    {
        $this->start("loadWebstoreConfig");
        /**
         * @var WebstoreConfigurationService $webstoreConfigService
         */
        $webstoreConfigService = pluginApp(WebstoreConfigurationService::class);
        $this->webstoreConfig = $webstoreConfigService->getWebstoreConfig();
        $this->track("loadWebstoreConfig");
    }
    
    private function getContactData($contactId)
    {
        $this->start("getContactData");
        /** @var AuthHelper $authHelper */
        $authHelper = pluginApp(AuthHelper::class);
        $contactRepo = $this->contactRepository;

        $contact = $authHelper->processUnguarded( function() use ($contactId, $contactRepo)
        {
            $this->track("getContactData");
            return $contactRepo->findContactById((int)$contactId);
        });

        $this->track("getContactData");
        return $contact;
    }
}