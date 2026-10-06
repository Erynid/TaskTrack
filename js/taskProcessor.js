// 1. KONSTANTA & ENUM
export const PRIORITY_WEIGHTS = Object.freeze({
    high: 3,
    medium: 2,
    low: 1
});

export const TASK_STATUSES = Object.freeze(['todo', 'inprogress', 'done', 'overdue']);

export const URGENCY_LEVELS = Object.freeze({
    CRITICAL: 'critical',
    WARNING: 'warning',
    SAFE: 'safe',
    OVERDUE: 'overdue',
    DONE: 'done'
});

// 2. ERROR HANDLING: KELAS ERROR KUSTOM
export class TaskDataError extends Error {
    constructor(message, field = null, value = null) {
        super(message);
        this.name = 'TaskDataError';
        this.field = field;
        this.value = value;
        this.timestamp = new Date().toISOString();
    }
}

// 3. KALKULASI WAKTU & STATUS
export const calculateRemainingHours = (deadlineStr, referenceDate = new Date()) => {
    if (!deadlineStr) {
        throw new TaskDataError('Deadline tidak boleh kosong untuk kalkulasi waktu.', 'deadline', deadlineStr);
    }
    const deadlineTime = new Date(deadlineStr).getTime();
    if (isNaN(deadlineTime)) {
        throw new TaskDataError(`Format deadline "${deadlineStr}" tidak valid.`, 'deadline', deadlineStr);
    }
    const refTime = referenceDate instanceof Date ? referenceDate.getTime() : new Date(referenceDate).getTime();
    const diffHours = (deadlineTime - refTime) / (1000 * 60 * 60);
    return Math.round(diffHours * 100) / 100;
};

export const isDeadlineOverdue = (deadlineStr, referenceDate = new Date()) => {
    try {
        return calculateRemainingHours(deadlineStr, referenceDate) < 0;
    } catch {
        return false;
    }
};

export const getUrgencyLevel = (deadlineStr, status = 'todo', referenceDate = new Date()) => {
    if (status === 'done') return URGENCY_LEVELS.DONE;
    if (status === 'overdue') return URGENCY_LEVELS.OVERDUE;

    try {
        const hours = calculateRemainingHours(deadlineStr, referenceDate);
        if (hours < 0) return URGENCY_LEVELS.OVERDUE;
        if (hours <= 24) return URGENCY_LEVELS.CRITICAL;
        if (hours <= 72) return URGENCY_LEVELS.WARNING;
        return URGENCY_LEVELS.SAFE;
    } catch {
        return URGENCY_LEVELS.SAFE;
    }
};

// 4. SANITASI INPUT & VALIDASI FIELD CLIENT-SIDE
export function sanitizeInput(str) {
    if (typeof str !== 'string') return '';
    return str
        .replace(/<[^>]*>/g, '')
        .trim();
}

export function validateTaskTitle(title) {
    if (!title || typeof title !== 'string' || !title.trim()) {
        return 'Judul tugas wajib diisi.';
    }
    const clean = title.trim();
    if (clean.length < 5) {
        return `Judul tugas minimal 5 karakter (saat ini: ${clean.length} karakter).`;
    }
    if (clean.length > 100) {
        return `Judul tugas maksimal 100 karakter (saat ini: ${clean.length} karakter).`;
    }
    return null;
}

export function validateTaskCourse(course) {
    if (!course || typeof course !== 'string' || !course.trim()) {
        return 'Nama mata kuliah wajib diisi.';
    }
    const clean = course.trim();
    if (clean.length < 3) {
        return `Nama mata kuliah minimal 3 karakter (saat ini: ${clean.length} karakter).`;
    }
    if (clean.length > 60) {
        return `Nama mata kuliah maksimal 60 karakter (saat ini: ${clean.length} karakter).`;
    }
    const validPattern = /^[a-zA-Z0-9\s()\-.,&/]+$/;
    if (!validPattern.test(clean)) {
        return 'Nama mata kuliah hanya boleh memuat huruf, angka, spasi, dan tanda baca umum.';
    }
    return null;
}

export function validateTaskDeadline(deadline, status = 'todo', isNew = false) {
    if (!deadline || typeof deadline !== 'string' || !deadline.trim()) {
        return 'Tenggat waktu (deadline) wajib diisi.';
    }
    const clean = deadline.trim();
    const parsedDate = Date.parse(clean);
    if (isNaN(parsedDate)) {
        return 'Format tanggal tenggat waktu tidak valid.';
    }
    const deadlineTime = new Date(clean).getTime();
    const nowTime = Date.now();
    if (isNew && deadlineTime < (nowTime - 60000)) {
        return 'Tenggat waktu untuk tugas baru tidak boleh berada di masa lampau.';
    }
    return null;
}

export function validateTaskPriority(priority) {
    const val = (priority || '').toLowerCase().trim();
    if (!Object.keys(PRIORITY_WEIGHTS).includes(val)) {
        return 'Prioritas harus salah satu dari: High, Medium, atau Low.';
    }
    return null;
}

export function validateTaskStatus(status) {
    const val = (status || '').toLowerCase().trim();
    if (!TASK_STATUSES.includes(val)) {
        return 'Status alur kerja harus salah satu dari: Planned, In progress, Completed, atau Overdue.';
    }
    return null;
}

export function validateTaskUrl(url) {
    if (!url || typeof url !== 'string' || !url.trim()) {
        return null;
    }
    const clean = url.trim();
    if (!/^https?:\/\//i.test(clean)) {
        return 'Tautan LMS harus diawali protokol http:// atau https:// (contoh: https://elearning.kampus.ac.id).';
    }
    try {
        const parsed = new URL(clean);
        if (!parsed.hostname || parsed.hostname.length < 3) {
            return 'Nama domain pada tautan LMS tidak valid.';
        }
    } catch {
        return 'Format tautan LMS tidak valid.';
    }
    return null;
}

export function validateTaskUrlLabel(label, url) {
    const cleanLabel = (label || '').trim();
    const cleanUrl = (url || '').trim();

    if (cleanLabel.length > 20) {
        return `Label pintasan maksimal 20 karakter (saat ini: ${cleanLabel.length} karakter).`;
    }
    if (cleanLabel && !cleanUrl) {
        return 'Tautan LMS wajib diisi jika label pintasan ditentukan.';
    }
    return null;
}

export function validateTaskNotes(notes) {
    if (!notes || typeof notes !== 'string') {
        return null;
    }
    const clean = notes.trim();
    if (clean.length > 500) {
        return `Catatan petunjuk tugas melebihi batas 500 karakter (saat ini: ${clean.length} karakter).`;
    }
    return null;
}

export function validateTaskFormData(formData, options = { isNew: true }) {
    if (!formData || typeof formData !== 'object') {
        return {
            isValid: false,
            errors: { form: 'Data formulir tidak valid.' },
            sanitizedData: null
        };
    }

    const isNew = options && options.isNew === true;
    const errors = {};

    const titleError = validateTaskTitle(formData.title);
    if (titleError) errors.title = titleError;

    const courseError = validateTaskCourse(formData.course);
    if (courseError) errors.course = courseError;

    const deadlineError = validateTaskDeadline(formData.deadline, formData.status, isNew);
    if (deadlineError) errors.deadline = deadlineError;

    const priorityError = validateTaskPriority(formData.priority);
    if (priorityError) errors.priority = priorityError;

    const statusError = validateTaskStatus(formData.status);
    if (statusError) errors.status = statusError;

    const urlError = validateTaskUrl(formData.lms_url);
    if (urlError) errors.lms_url = urlError;

    const urlLabelError = validateTaskUrlLabel(formData.lms_label, formData.lms_url);
    if (urlLabelError) errors.lms_label = urlLabelError;

    const notesError = validateTaskNotes(formData.instructions);
    if (notesError) errors.instructions = notesError;

    const isValid = Object.keys(errors).length === 0;

    const sanitizedData = {
        id: (formData.id || '').trim(),
        title: sanitizeInput(formData.title || ''),
        course: sanitizeInput(formData.course || ''),
        deadline: (formData.deadline || '').trim(),
        priority: ((formData.priority || 'medium').toLowerCase()).trim(),
        status: ((formData.status || 'todo').toLowerCase()).trim(),
        lms_url: (formData.lms_url || '').trim(),
        lms_label: sanitizeInput(formData.lms_label || '') || ((formData.lms_url || '').trim() ? 'LMS' : ''),
        instructions: sanitizeInput(formData.instructions || '')
    };

    return {
        isValid,
        errors,
        sanitizedData
    };
}

export function validateTask(task) {
    if (!task || typeof task !== 'object' || Array.isArray(task)) {
        throw new TaskDataError('Tugas harus berupa objek valid.', 'task', task);
    }

    if (!task.id || typeof task.id !== 'string' || !task.id.trim()) {
        throw new TaskDataError('ID tugas wajib berupa string tidak kosong.', 'id', task.id);
    }

    const validationResult = validateTaskFormData(task, { isNew: false });
    if (!validationResult.isValid) {
        const firstField = Object.keys(validationResult.errors)[0];
        const firstMsg = validationResult.errors[firstField];
        throw new TaskDataError(firstMsg, firstField, task[firstField]);
    }

    return {
        ...task,
        ...validationResult.sanitizedData
    };
}

export function validateTaskBatch(tasks) {
    if (!Array.isArray(tasks)) {
        throw new TaskDataError('Parameter tasks harus berupa Array.', 'tasks', tasks);
    }

    return tasks.every((task, index) => {
        try {
            validateTask(task);
            return true;
        } catch (err) {
            throw new TaskDataError(
                `Tugas pada indeks [${index}] tidak valid: ${err.message}`,
                `tasks[${index}]`,
                task
            );
        }
    });
}

// 5. PEMFILTERAN & PENCARIAN TUGAS
export function filterTasks(tasks, criteria = {}, referenceDate = new Date()) {
    if (!Array.isArray(tasks)) {
        throw new TaskDataError('Parameter tasks harus berupa Array.', 'tasks', tasks);
    }

    const {
        course = 'all',
        status = 'all',
        priority = 'all',
        search = '',
        isOverdue = null,
        isUrgent = null
    } = criteria;

    const query = typeof search === 'string' ? search.toLowerCase().trim() : '';

    return tasks.filter(task => {
        if (course !== 'all' && task.course !== course) return false;
        if (status !== 'all' && task.status !== status) return false;
        if (priority !== 'all' && (task.priority || 'medium').toLowerCase() !== priority.toLowerCase()) return false;

        if (typeof isOverdue === 'boolean') {
            const taskIsOverdue = task.status === 'overdue' || isDeadlineOverdue(task.deadline, referenceDate);
            if (isOverdue !== taskIsOverdue) return false;
        }

        if (typeof isUrgent === 'boolean') {
            let taskIsUrgent = false;
            if (task.status !== 'done') {
                try {
                    const remainingHours = calculateRemainingHours(task.deadline, referenceDate);
                    taskIsUrgent = remainingHours <= 24;
                } catch {
                    taskIsUrgent = false;
                }
            }
            if (isUrgent !== taskIsUrgent) return false;
        }

        if (query) {
            const titleMatch = (task.title || '').toLowerCase().includes(query);
            const courseMatch = (task.course || '').toLowerCase().includes(query);
            const instMatch = (task.instructions || '').toLowerCase().includes(query);
            if (!titleMatch && !courseMatch && !instMatch) return false;
        }

        return true;
    });
}

export const getUrgentTasks = (tasks, thresholdHours = 24, referenceDate = new Date()) => {
    if (!Array.isArray(tasks)) {
        throw new TaskDataError('Parameter tasks harus berupa Array.', 'tasks', tasks);
    }
    return tasks.filter(task => {
        if (task.status === 'done') return false;
        try {
            const hours = calculateRemainingHours(task.deadline, referenceDate);
            return hours <= thresholdHours;
        } catch {
            return false;
        }
    });
};

export const getOverdueTasks = (tasks, referenceDate = new Date()) => {
    if (!Array.isArray(tasks)) {
        throw new TaskDataError('Parameter tasks harus berupa Array.', 'tasks', tasks);
    }
    return tasks.filter(task => {
        if (task.status === 'done') return false;
        if (task.status === 'overdue') return true;
        return isDeadlineOverdue(task.deadline, referenceDate);
    });
};

// 6. TRANSFORMASI & FORMATTING DATA
export function enrichTasks(tasks, referenceDate = new Date()) {
    if (!Array.isArray(tasks)) {
        throw new TaskDataError('Parameter tasks harus berupa Array.', 'tasks', tasks);
    }

    return tasks.map(task => {
        let remainingHours = 0;
        let isOverdue = false;
        try {
            remainingHours = calculateRemainingHours(task.deadline, referenceDate);
            isOverdue = remainingHours < 0;
        } catch {
            remainingHours = 0;
            isOverdue = false;
        }

        const urgency = getUrgencyLevel(task.deadline, task.status, referenceDate);
        const isUrgent = task.status !== 'done' && (remainingHours <= 24 || isOverdue);

        return {
            ...task,
            remainingHours,
            urgency,
            isOverdue,
            isUrgent,
            priorityWeight: PRIORITY_WEIGHTS[(task.priority || 'medium').toLowerCase()] || 1
        };
    });
}

export const getUniqueCourses = (tasks) => {
    if (!Array.isArray(tasks)) return [];
    const allCourses = tasks
        .map(t => (t && typeof t.course === 'string' ? t.course.trim() : ''))
        .filter(course => course.length > 0);

    return Array.from(new Set(allCourses)).sort((a, b) => a.localeCompare(b));
};

// 7. PENCARIAN TUGAS TUNGGAL
export const findTaskById = (tasks, id) => {
    if (!Array.isArray(tasks) || !id) return null;
    return tasks.find(task => task.id === id) || null;
};

export const findTaskIndex = (tasks, id) => {
    if (!Array.isArray(tasks) || !id) return -1;
    return tasks.findIndex(task => task.id === id);
};

// 8. VERIFIKASI KEBERADAAN DATA
export const hasCriticalDeadlines = (tasks, thresholdHours = 24, referenceDate = new Date()) => {
    if (!Array.isArray(tasks) || tasks.length === 0) return false;

    return tasks.some(task => {
        if (task.status === 'done') return false;
        if (task.status === 'overdue') return true;
        try {
            const hours = calculateRemainingHours(task.deadline, referenceDate);
            return hours <= thresholdHours;
        } catch {
            return false;
        }
    });
};

// 9. PENGURUTAN DATA
export function sortTasks(tasks, sortBy = 'deadline', order = 'asc') {
    if (!Array.isArray(tasks)) {
        throw new TaskDataError('Parameter tasks harus berupa Array.', 'tasks', tasks);
    }

    const direction = order.toLowerCase() === 'desc' ? -1 : 1;
    const cloned = [...tasks];

    return cloned.sort((a, b) => {
        switch (sortBy) {
            case 'deadline': {
                const timeA = new Date(a.deadline).getTime() || 0;
                const timeB = new Date(b.deadline).getTime() || 0;
                return (timeA - timeB) * direction;
            }
            case 'priority': {
                const weightA = PRIORITY_WEIGHTS[(a.priority || 'medium').toLowerCase()] || 1;
                const weightB = PRIORITY_WEIGHTS[(b.priority || 'medium').toLowerCase()] || 1;
                return (weightA - weightB) * direction;
            }
            case 'title': {
                const titleA = (a.title || '').toLowerCase();
                const titleB = (b.title || '').toLowerCase();
                return titleA.localeCompare(titleB) * direction;
            }
            case 'course': {
                const courseA = (a.course || '').toLowerCase();
                const courseB = (b.course || '').toLowerCase();
                return courseA.localeCompare(courseB) * direction;
            }
            default:
                return 0;
        }
    });
}

export const getTopUrgentTasks = (tasks, limit = 4, referenceDate = new Date()) => {
    if (!Array.isArray(tasks)) return [];
    const urgentTasks = getUrgentTasks(tasks, 24, referenceDate);
    const sorted = sortTasks(urgentTasks, 'deadline', 'asc');
    return sorted.slice(0, Math.max(0, limit));
};

// 10. METRIK STATISTIK & AGREGASI
export function calculateTaskStatistics(tasks, referenceDate = new Date()) {
    if (!Array.isArray(tasks)) {
        throw new TaskDataError('Parameter tasks harus berupa Array.', 'tasks', tasks);
    }

    const initialStats = {
        total: 0,
        byStatus: { todo: 0, inprogress: 0, done: 0, overdue: 0 },
        byPriority: { high: 0, medium: 0, low: 0 },
        completedCount: 0,
        activeCount: 0,
        overdueCount: 0,
        urgentCount: 0,
        completionRate: 0
    };

    if (tasks.length === 0) return initialStats;

    const stats = tasks.reduce((acc, task) => {
        acc.total += 1;

        const status = (task.status || 'todo').toLowerCase();
        if (acc.byStatus[status] !== undefined) {
            acc.byStatus[status] += 1;
        } else {
            acc.byStatus.todo += 1;
        }

        const priority = (task.priority || 'medium').toLowerCase();
        if (acc.byPriority[priority] !== undefined) {
            acc.byPriority[priority] += 1;
        } else {
            acc.byPriority.medium += 1;
        }

        if (status === 'done') {
            acc.completedCount += 1;
        } else {
            acc.activeCount += 1;

            let remainingHours = Infinity;
            try {
                remainingHours = calculateRemainingHours(task.deadline, referenceDate);
            } catch {
                remainingHours = Infinity;
            }

            if (status === 'overdue' || remainingHours < 0) {
                acc.overdueCount += 1;
            }

            if (remainingHours >= 0 && remainingHours <= 24) {
                acc.urgentCount += 1;
            }
        }

        return acc;
    }, initialStats);

    stats.completionRate = stats.total > 0
        ? Math.round((stats.completedCount / stats.total) * 1000) / 10
        : 0;

    return stats;
}

// 11. PENGELOMPOKAN DATA
export function groupTasksByCourse(tasks) {
    if (!Array.isArray(tasks)) {
        throw new TaskDataError('Parameter tasks harus berupa Array.', 'tasks', tasks);
    }

    const groupedMap = tasks.reduce((acc, task) => {
        const courseName = (task.course || 'Tanpa Mata Kuliah').trim();

        if (!acc[courseName]) {
            acc[courseName] = {
                course: courseName,
                total: 0,
                todo: 0,
                inprogress: 0,
                done: 0,
                overdue: 0,
                completionRate: 0,
                tasks: []
            };
        }

        acc[courseName].total += 1;
        acc[courseName].tasks.push(task);

        const status = (task.status || 'todo').toLowerCase();
        if (status === 'done') acc[courseName].done += 1;
        else if (status === 'inprogress') acc[courseName].inprogress += 1;
        else if (status === 'overdue') acc[courseName].overdue += 1;
        else acc[courseName].todo += 1;

        return acc;
    }, {});

    return Object.values(groupedMap).map(item => ({
        ...item,
        completionRate: item.total > 0
            ? Math.round((item.done / item.total) * 1000) / 10
            : 0
    })).sort((a, b) => a.course.localeCompare(b.course));
}

export function groupTasksByDate(tasks) {
    if (!Array.isArray(tasks)) return {};

    return tasks.reduce((acc, task) => {
        if (!task.deadline) return acc;
        const dateKey = task.deadline.slice(0, 10);
        if (!acc[dateKey]) {
            acc[dateKey] = [];
        }
        acc[dateKey].push(task);
        return acc;
    }, {});
}

// 12. PARSER & SERIALISASI JSON
export function parseTasksFromJSON(jsonString, fallbackTasks = []) {
    if (!jsonString || typeof jsonString !== 'string') {
        return Array.isArray(fallbackTasks) ? fallbackTasks : [];
    }

    try {
        const parsed = JSON.parse(jsonString);

        if (!Array.isArray(parsed)) {
            console.warn('[TaskProcessor] Data bukan Array, fallback digunakan.');
            return fallbackTasks;
        }

        const validatedTasks = parsed
            .map((item, idx) => {
                try {
                    return validateTask(item);
                } catch (itemErr) {
                    console.warn(`[TaskProcessor] Item indeks ${idx} diabaikan: ${itemErr.message}`);
                    return null;
                }
            })
            .filter(Boolean);

        return validatedTasks.length > 0 ? validatedTasks : fallbackTasks;
    } catch (parseError) {
        console.error('[TaskProcessor] Gagal parse JSON data tugas:', parseError.message);
        return fallbackTasks;
    }
}

export function exportTasksToJSON(tasks) {
    if (!Array.isArray(tasks)) {
        throw new TaskDataError('Data yang diekspor harus berupa Array.', 'tasks', tasks);
    }

    try {
        return JSON.stringify(tasks, null, 2);
    } catch (err) {
        throw new TaskDataError(`Gagal ekspor tugas ke JSON: ${err.message}`, 'json', err);
    }
}

// 13. EXPORT MODUL
const taskProcessor = {
    PRIORITY_WEIGHTS,
    TASK_STATUSES,
    URGENCY_LEVELS,
    TaskDataError,
    calculateRemainingHours,
    isDeadlineOverdue,
    getUrgencyLevel,
    sanitizeInput,
    validateTaskTitle,
    validateTaskCourse,
    validateTaskDeadline,
    validateTaskPriority,
    validateTaskStatus,
    validateTaskUrl,
    validateTaskUrlLabel,
    validateTaskNotes,
    validateTaskFormData,
    validateTask,
    validateTaskBatch,
    filterTasks,
    getUrgentTasks,
    getOverdueTasks,
    enrichTasks,
    getUniqueCourses,
    findTaskById,
    findTaskIndex,
    hasCriticalDeadlines,
    sortTasks,
    getTopUrgentTasks,
    calculateTaskStatistics,
    groupTasksByCourse,
    groupTasksByDate,
    parseTasksFromJSON,
    exportTasksToJSON
};

if (typeof window !== 'undefined') {
    window.TaskProcessor = taskProcessor;
}

export default taskProcessor;
