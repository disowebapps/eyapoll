<?php

namespace App\Livewire\Admin\Notification;

use App\Models\EmailTemplate;
use App\Models\SmsTemplate;
use App\Models\InAppTemplate;
use App\Enums\Notification\NotificationEventType;
use App\Enums\Notification\NotificationChannel;
use App\Livewire\Admin\BaseAdminComponent;
use Illuminate\Validation\Rule;

class NotificationTemplates extends BaseAdminComponent
{
    public $activeTab = 'email';
    public $templates = [];
    public $selectedTemplate = null;
    public $showEditModal = false;

    // Form fields
    public $templateId = null;
    public $eventType = '';
    public $channel = '';
    public $subject = '';
    public $body = '';
    public $isActive = true;
    public $estimatedCost = 0.0;

    protected $rules = [
        'eventType' => 'required|string',
        'channel' => 'required|string',
        'subject' => 'required_if:channel,email|string|max:255',
        'body' => 'required|string|max:1000',
        'isActive' => 'boolean',
        'estimatedCost' => 'numeric|min:0|max:10',
    ];

    public function mount()
    {
        $this->authorize('viewAny', EmailTemplate::class);
        $this->loadTemplates();
    }

    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
        $this->loadTemplates();
    }

    public function loadTemplates()
    {
        switch ($this->activeTab) {
            case 'email':
                $this->templates = EmailTemplate::with('eventType')->get();
                break;
            case 'sms':
                $this->templates = SmsTemplate::with('eventType')->get();
                break;
            case 'in_app':
                $this->templates = InAppTemplate::with('eventType')->get();
                break;
        }
    }

    public function createTemplate()
    {
        $this->resetForm();
        $this->showEditModal = true;
    }

    public function editTemplate($templateId)
    {
        $this->loadTemplate($templateId);
        $this->showEditModal = true;
    }

    public function loadTemplate($templateId)
    {
        $model = $this->getTemplateModel();
        $template = $model::findOrFail($templateId);

        $this->templateId = $template->id;
        $this->eventType = $template->event_type;
        $this->channel = $this->activeTab;
        $this->subject = $template->subject ?? '';
        $this->body = $template->body ?? $template->message ?? '';
        $this->isActive = $template->is_active;
        $this->estimatedCost = $template->estimated_cost ?? 0.0;
    }

    public function saveTemplate()
    {
        $this->validate();

        $model = $this->getTemplateModel();
        $data = [
            'event_type' => $this->eventType,
            'is_active' => $this->isActive,
        ];

        if ($this->activeTab === 'email') {
            $data['subject'] = $this->subject;
            $data['body'] = $this->body;
        } elseif ($this->activeTab === 'sms') {
            $data['message'] = $this->body;
            $data['estimated_cost'] = $this->estimatedCost;
        } else {
            $data['message'] = $this->body;
        }

        if ($this->templateId) {
            $model::where('id', $this->templateId)->update($data);
            session()->flash('success', 'Template updated successfully.');
        } else {
            $model::create($data);
            session()->flash('success', 'Template created successfully.');
        }

        $this->closeModal();
        $this->loadTemplates();
    }

    public function deleteTemplate($templateId)
    {
        $model = $this->getTemplateModel();
        $model::findOrFail($templateId)->delete();

        session()->flash('success', 'Template deleted successfully.');
        $this->loadTemplates();
    }

    public function toggleTemplate($templateId)
    {
        $model = $this->getTemplateModel();
        $template = $model::findOrFail($templateId);
        $template->update(['is_active' => !$template->is_active]);

        session()->flash('success', 'Template status updated successfully.');
        $this->loadTemplates();
    }

    private function getTemplateModel()
    {
        return match ($this->activeTab) {
            'email' => EmailTemplate::class,
            'sms' => SmsTemplate::class,
            'in_app' => InAppTemplate::class,
        };
    }

    private function resetForm()
    {
        $this->templateId = null;
        $this->eventType = '';
        $this->channel = $this->activeTab;
        $this->subject = '';
        $this->body = '';
        $this->isActive = true;
        $this->estimatedCost = 0.0;
    }

    public function closeModal()
    {
        $this->showEditModal = false;
        $this->resetForm();
    }

    public function render()
    {
        return view('livewire.admin.notification.notification-templates', [
            'eventTypes' => NotificationEventType::cases(),
        ]);
    }
}
