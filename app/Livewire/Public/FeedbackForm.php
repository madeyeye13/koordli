<?php

namespace App\Livewire\Public;

use App\Jobs\SendFeedbackSubmissionNotificationJob;
use App\Models\Central\FeedbackSubmission;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.landing')]
class FeedbackForm extends Component
{
    public string $name = '';
    public string $experience = '';
    public ?int $navigationRating = null;
    public ?int $clarityRating = null;
    public array $mostUseful = [];
    public string $hadConfusion = '';
    public string $confusionDetails = '';
    public string $improvement = '';
    public ?int $likelihood = null;
    public ?int $overallRating = null;

    public function submit(): void
    {
        $data = $this->validate([
            'name' => 'required|string|min:2|max:150',
            'experience' => 'required|in:event_planner,wedding_planner,event_company,corporate,church,other',
            'navigationRating' => 'required|integer|min:1|max:5',
            'clarityRating' => 'required|integer|min:1|max:5',
            'mostUseful' => 'required|array|min:1',
            'mostUseful.*' => 'in:events,tasks,vendors,budget,contracts,rsvp,runsheet,client_portal,bookings,other',
            'hadConfusion' => 'required|in:0,1',
            'confusionDetails' => 'required_if:hadConfusion,1|nullable|string|max:5000',
            'improvement' => 'required|string|min:5|max:5000',
            'likelihood' => 'required|integer|min:1|max:5',
            'overallRating' => 'required|integer|min:1|max:5',
        ], [
            'confusionDetails.required_if' => 'Please tell us what happened so we can investigate it.',
        ]);

        $feedback = FeedbackSubmission::create([
            'name' => $data['name'],
            'experience' => $data['experience'],
            'navigation_rating' => $data['navigationRating'],
            'clarity_rating' => $data['clarityRating'],
            'most_useful' => $data['mostUseful'],
            'had_confusion' => (bool) $data['hadConfusion'],
            'confusion_details' => $data['confusionDetails'] ?: null,
            'improvement' => $data['improvement'],
            'likelihood' => $data['likelihood'],
            'overall_rating' => $data['overallRating'],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        SendFeedbackSubmissionNotificationJob::dispatch($feedback);

        $this->redirectRoute('feedback.thanks', navigate: true);
    }

    public function render()
    {
        return view('livewire.public.feedback-form');
    }
}
