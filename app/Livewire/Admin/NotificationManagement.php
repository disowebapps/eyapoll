<?php

namespace App\Livewire\Admin;

use Livewire\WithPagination;
use App\Models\Notification\Notification;
use App\Models\Notification\NotificationTemplate;
use App\Models\User;
use App\Models\Candidate\Candidate;
use App\Models\Observer;

class NotificationManagement extends BaseAdminComponent
{
    use WithPagination;

    public $activeTab = 'send';
    public $showTemplateModal = false;
    public $editingTemplate = null;

    // Send notification form
    public $recipient_type = 'all_users';
    public $subject = '';
    public $message = '';
    public $template_id = '';
    public $schedule_at = '';

    // Template form
    public $template_name = '';
    public $template_subject = '';
    public $template_body = '';
    public $template_type = 'general';

    protected $rules = [
        'subject' => 'required|string|max:255',
        'message' => 'required|string|max:2000',
        'recipient_type' => 'required|string',
        'schedule_at' => 'nullable|date|after:now',
    ];

    protected $templateRules = [
        'template_name' => 'required|string|max:255',
        'template_subject' => 'required|string|max:255',
        'template_body' => 'required|string|max:2000',
        'template_type' => 'required|in:general,election,candidate,voter',
    ];

    public function mount()
    {
        // Set default permissions if missing
        if (auth('admin')->user() && !auth('admin')->user()->permissions) {
            auth('admin')->user()->update([
                'permissions' => ['manage_elections', 'manage_users', 'system_settings', 'manage_notifications']
            ]);
        }
    }

    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function loadTemplate()
    {
        if ($this->template_id) {
            $template = NotificationTemplate::find($this->template_id);
            $this->subject = $template->subject;
            $this->message = $template->body;
        }
    }

    public function sendNotification()
    {
        $this->validate();

        $recipients = $this->getRecipients();
        
        if ($recipients->isEmpty()) {
            session()->flash('error', 'No recipients found for the selected criteria.');
            return;
        }

        foreach ($recipients as $recipient) {
            Notification::create([
                'user_id' => $recipient->id,
                'user_type' => $this->getUserType($recipient),
                'type' => 'admin_message',
                'title' => $this->subject,
                'message' => $this->message,
                'scheduled_at' => $this->schedule_at ?: now(),
                'created_by' => auth()->id(),
            ]);
        }

        $count = $recipients->count();
        $message = $this->schedule_at 
            ? "Notification scheduled for {$count} recipients."
            : "Notification sent to {$count} recipients.";

        session()->flash('success', $message);
        $this->resetNotificationForm();
    }

    private function getRecipients()
    {
        return match($this->recipient_type) {
            'all_users' => User::approved()->get(),
            'voters' => User::approved()->get(),
            'candidates' => Candidate::approved()->get(),
            'observers' => Observer::approved()->get(),
            'pending_users' => User::pending()->get(),
            'pending_candidates' => Candidate::pending()->get(),
            default => collect(),
        };
    }

    private function getUserType($user)
    {
        return match(get_class($user)) {
            User::class => 'voter',
            Candidate::class => 'candidate',
            Observer::class => 'observer',
            default => 'user',
        };
    }

    public function resetNotificationForm()
    {
        $this->subject = '';
        $this->message = '';
        $this->template_id = '';
        $this->schedule_at = '';
        $this->recipient_type = 'all_users';
    }

    public function openTemplateModal()
    {
        $this->resetTemplateForm();
        $this->showTemplateModal = true;
    }

    public function editTemplate($id)
    {
        $template = NotificationTemplate::find($id);
        $this->editingTemplate = $id;
        $this->template_name = $template->event_type;
        $this->template_subject = $template->subject;
        $this->template_body = $template->body_template;
        $this->template_type = $template->event_type;
        $this->showTemplateModal = true;
    }

    public function saveTemplate()
    {
        try {
            $this->validate($this->templateRules);

            $data = [
                'event_type' => $this->template_type,
                'channel' => 'email',
                'subject' => $this->template_subject,
                'body_template' => $this->template_body,
                'is_active' => true,
                'created_by' => auth('admin')->id(),
            ];

            if ($this->editingTemplate) {
                NotificationTemplate::find($this->editingTemplate)->update($data);
                $message = 'Template updated successfully!';
            } else {
                NotificationTemplate::create($data);
                $message = 'Template created successfully!';
            }

            session()->flash('success', $message);
            $this->closeTemplateModal();
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to save template: ' . $e->getMessage());
        }
    }

    public function deleteTemplate($id)
    {
        NotificationTemplate::find($id)->delete();
        session()->flash('success', 'Template deleted successfully.');
    }

    public function closeTemplateModal()
    {
        $this->showTemplateModal = false;
        $this->editingTemplate = null;
        $this->resetTemplateForm();
    }

    private function resetTemplateForm()
    {
        $this->template_name = '';
        $this->template_subject = '';
        $this->template_body = '';
        $this->template_type = 'general';
    }

    public function render()
    {
        $templates = NotificationTemplate::orderBy('event_type')->get();
        $notifications = Notification::with(['user'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('livewire.admin.notification-management', compact('templates', 'notifications'));
    }
}