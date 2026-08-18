function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
}

async function postJson(url, payload) {
    const response = await fetch(url, {
        method: 'POST',
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            ...(csrfToken() ? { 'X-CSRF-TOKEN': csrfToken() } : {}),
        },
        body: JSON.stringify(payload),
    });

    const data = await response.json().catch(() => ({}));

    if (!response.ok) {
        const message = data?.message || 'Unable to save item.';
        const error = new Error(message);
        error.response = data;
        throw error;
    }

    return data;
}

function addOrUpdateSelectOption(selectId, item) {
    const select = document.getElementById(selectId);
    if (!select) {
        return;
    }

    const existing = Array.from(select.options).find((option) => String(option.value) === String(item.id));
    if (existing) {
        existing.text = item.name;
    } else {
        select.add(new Option(item.name, item.id, true, true));
    }

    select.value = String(item.id);
    select.dispatchEvent(new Event('change', { bubbles: true }));
}

window.liveLessonSetup = function liveLessonSetup(config = {}) {
    return {
        ...config,
        audienceMode: config.audienceMode || 'group',
        startOption: config.startOption || 'start_now',
        initialMode: config.initialMode || 'whiteboard',
        selectedCourseId: config.selectedCourseId || '',
        selectedSubjectId: config.selectedSubjectId || '',
        selectedClassId: config.selectedClassId || '',
        selectedLearnerIds: Array.isArray(config.selectedLearnerIds) ? config.selectedLearnerIds : [],
        permissions: {
            allow_student_draw: true,
            allow_student_type: true,
            allow_student_code: true,
            allow_student_chat: true,
            show_pointer: true,
            allow_resource_download: false,
            ...(config.permissions || {}),
        },
        courseDrawerOpen: false,
        subjectDrawerOpen: false,
        courseForm: { name: '', description: '' },
        subjectForm: { name: '', description: '' },
        courseStatus: '',
        subjectStatus: '',
        busyCourse: false,
        busySubject: false,

        toggleAudience(mode) {
            this.audienceMode = mode;
        },

        async createCourse() {
            if (!this.courseForm.name.trim()) {
                this.courseStatus = 'Please add a course name.';
                return;
            }

            this.busyCourse = true;
            this.courseStatus = 'Saving course...';

            try {
                const data = await postJson(this.createCourseUrl, this.courseForm);
                const course = data.course;
                if (course) {
                    addOrUpdateSelectOption('lesson-course-select', course);
                    this.selectedCourseId = String(course.id);
                }
                this.courseForm = { name: '', description: '' };
                this.courseDrawerOpen = false;
                this.courseStatus = 'Course added.';
            } catch (error) {
                this.courseStatus = error.message || 'Unable to save course.';
            } finally {
                this.busyCourse = false;
            }
        },

        async createSubject() {
            if (!this.subjectForm.name.trim()) {
                this.subjectStatus = 'Please add a subject name.';
                return;
            }

            this.busySubject = true;
            this.subjectStatus = 'Saving subject...';

            try {
                const data = await postJson(this.createSubjectUrl, {
                    ...this.subjectForm,
                    course_id: this.selectedCourseId || null,
                });
                const subject = data.subject;
                if (subject) {
                    addOrUpdateSelectOption('lesson-subject-select', subject);
                    this.selectedSubjectId = String(subject.id);
                }
                this.subjectForm = { name: '', description: '' };
                this.subjectDrawerOpen = false;
                this.subjectStatus = 'Subject added.';
            } catch (error) {
                this.subjectStatus = error.message || 'Unable to save subject.';
            } finally {
                this.busySubject = false;
            }
        },
    };
};
