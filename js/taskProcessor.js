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

// 4. VALIDASI DATA TUGAS
export function validateTask(task) {
    if (!task || typeof task !== 'object' || Array.isArray(task)) {
        throw new TaskDataError('Tugas harus berupa objek valid.', 'task', task);
    }

    if (!task.id || typeof task.id !== 'string' || !task.id.trim()) {
        throw new TaskDataError('ID tugas wajib berupa string tidak kosong.', 'id', task.id);
    }

    if (!task.title || typeof task.title !== 'string' || !task.title.trim()) {
        throw new TaskDataError('Judul tugas (title) wajib diisi.', 'title', task.title);
    }

    if (!task.course || typeof task.course !== 'string' || !task.course.trim()) {
        throw new TaskDataError('Mata kuliah (course) wajib diisi.', 'course', task.course);
    }

    if (!task.deadline || typeof task.deadline !== 'string') {
        throw new TaskDataError('Deadline tugas wajib diisi string tanggal.', 'deadline', task.deadline);
    }
    const parsedDate = Date.parse(task.deadline);
    if (isNaN(parsedDate)) {
        throw new TaskDataError(`Format deadline "${task.deadline}" tidak valid.`, 'deadline', task.deadline);
    }

    const normalizedStatus = (task.status || 'todo').toLowerCase();
    if (!TASK_STATUSES.includes(normalizedStatus)) {
        throw new TaskDataError(
            `Status "${task.status}" tidak dikenal. Status yang valid: ${TASK_STATUSES.join(', ')}`,
            'status',
            task.status
        );
    }

    const normalizedPriority = (task.priority || 'medium').toLowerCase();
    if (!Object.keys(PRIORITY_WEIGHTS).includes(normalizedPriority)) {
        throw new TaskDataError(
            `Prioritas "${task.priority}" tidak dikenal. Prioritas yang valid: high, medium, low`,
            'priority',
            task.priority
        );
    }

    return {
        ...task,
        id: task.id.trim(),
        title: task.title.trim(),
        course: task.course.trim(),
        deadline: task.deadline.trim(),
        status: normalizedStatus,
        priority: normalizedPriority,
        lms_url: typeof task.lms_url === 'string' ? task.lms_url.trim() : '',
        lms_label: typeof task.lms_label === 'string' ? task.lms_label.trim() : 'LMS',
        instructions: typeof task.instructions === 'string' ? task.instructions.trim() : ''
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
