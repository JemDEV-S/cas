@extends('applicantportal::components.layouts.master')

@section('title', 'Postular a ' . $jobProfile->profile_name)

@push('styles')
<style>
    .step-indicator {
        transition: all 0.3s ease;
    }
    .step-indicator.active {
        transform: scale(1.1);
    }
    .step-indicator.completed {
        background: linear-gradient(135deg, #10B981 0%, #059669 100%);
    }
    .fade-in {
        animation: fadeIn 0.3s ease-in;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    [x-cloak] {
        display: none !important;
    }
</style>
@endpush

@section('content')
<div class="max-w-6xl mx-auto" x-data="applicationWizard()">

    @include('applicantportal::job-postings.partials._autocomplete-modal')

    @include('applicantportal::job-postings.partials._header')

    @include('applicantportal::job-postings.partials._progress-bar')

    <form method="POST"
          id="application-form"
          action="{{ route('applicant.job-postings.apply.store', [$posting->id, $jobProfile->id]) }}"
          @submit.prevent="submitApplication"
          class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
        @csrf

        @include('applicantportal::job-postings.partials._step-1-personal')
        @include('applicantportal::job-postings.partials._step-2-academic')
        @include('applicantportal::job-postings.partials._step-3-experience')
        @include('applicantportal::job-postings.partials._step-4-training')
        @include('applicantportal::job-postings.partials._step-5-knowledge')
        @include('applicantportal::job-postings.partials._step-6-registrations')
        @include('applicantportal::job-postings.partials._step-7-conditions')
        @include('applicantportal::job-postings.partials._step-8-review')

        @include('applicantportal::job-postings.partials._nav-buttons')
    </form>
</div>
@endsection

@push('scripts')
<script>
    window.wizardConfig = {
        jobProfileId: @json($jobProfile->id),
        userId: @json(auth()->id()),
        storeUrl: @json(route('applicant.job-postings.apply.store', [$posting->id, $jobProfile->id])),
        csrfToken: @json(csrf_token()),
        acceptedCareerIds: @json($acceptedCareerIds),
        educationLevels: @json($educationLevels),
        minimumEducationLevel: @json($minimumEducationLevel ? $minimumEducationLevel->value : null),
        minimumEducationLevelValue: @json($minimumEducationLevel ? $minimumEducationLevel->level() : 0),
        requiredCoursesComplianceInitial: @json($requiredCoursesComplianceInitial),
        knowledgeComplianceInitial: @json($knowledgeComplianceInitial),
        requiredCourses: @json($jobProfile->required_courses ?? []),
        colegiaturaRequired: @json((bool) $jobProfile->colegiatura_required),
        generalExperienceYears: @json($jobProfile->general_experience_years ? (is_object($jobProfile->general_experience_years) ? $jobProfile->general_experience_years->toDecimal() : $jobProfile->general_experience_years) : 0),
        specificExperienceYears: @json($jobProfile->specific_experience_years ? (is_object($jobProfile->specific_experience_years) ? $jobProfile->specific_experience_years->toDecimal() : $jobProfile->specific_experience_years) : 0),
        user: {
            fullName: @json(trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''))),
            dni: @json($user->dni ?? ''),
            birthDate: @json($user->birth_date ? \Carbon\Carbon::parse($user->birth_date)->format('Y-m-d') : ''),
            address: @json($user->address ?? ''),
            phone: @json($user->phone ?? ''),
            email: @json($user->email ?? ''),
        },
        previousApplicationData: @json($previousApplicationData),
        draftApplicationData: @json($draftApplicationData ?? null),
    };
</script>
<script src="{{ asset('js/application-wizard.js') }}"></script>
@endpush
