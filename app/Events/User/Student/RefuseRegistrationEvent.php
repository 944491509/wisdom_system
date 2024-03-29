<?php

namespace App\Events\User\Student;

use App\Models\RecruitStudent\RegistrationInformatics;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use App\Dao\Schools\SchoolDao;
use App\Dao\Schools\MajorDao;
use App\Dao\Schools\OrganizationDao;

class RefuseRegistrationEvent extends AbstractRegistrationEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    /**
     * RefuseRegistrationEvent constructor.
     * @param RegistrationInformatics $form
     */
    public function __construct(RegistrationInformatics $form)
    {
        parent::__construct($form);
    }


    public function getSmsTemplateId(): string
    {
        return  '483488';
    }

    public function getSmsContent(): array
    {
        return [$this->getUserName(), $this->getSchoolName(), $this->getMajorName(), '不通过', $this->getAdmissionOfficeMobile()];
    }

    public function getSchoolName()
    {
        $dao = new SchoolDao;
        $result = $dao->getSchoolById($this->form->school_id);
        if ($result) {
            return $result->name;
        }
    }

    public function getMajorName()
    {
        $dao = new MajorDao;
        $result = $dao->getMajorById($this->form->major_id);
        if ($result) {
            return $result->name;
        }
    }

    public function getAdmissionOfficeMobile()
    {
        $dao = new OrganizationDao;
        $result = $dao->getByName($this->form->school_id, '招生办');
        if ($result) {
            return $result->phone;
        }
    }

    public function getForm(): RegistrationInformatics
    {
        // TODO: Implement getForm() method.
    }

    public function getMessageType(): int
    {
        // TODO: Implement getMessageType() method.
    }

    public function getPriority(): int
    {
        // TODO: Implement getPriority() method.
    }

    public function getSystemContent(): string
    {
        // TODO: Implement getContent() method.
    }

    public function getNextMove(): string
    {
        // TODO: Implement getNextMove() method.
    }


    /**
     * @inheritDoc
     */
    public function getAction(): int
    {
        // TODO: Implement getAction() method.
    }
}
