/**
 * TaskTrack - Modul Pengolahan Data Tugas (Task Processor)
 * Modul JavaScript ES6 untuk manipulasi, pemfilteran, pengurutan, kalkulasi statistik,
 * dan validasi array of objects tugas perkuliahan dengan penanganan error terstruktur.
 *
 * Menggunakan:
 * - Array of Objects
 * - Function & Arrow Function
 * - Array Methods: .filter(), .map(), .reduce(), .find(), .findIndex(), .some(), .every(), .sort(), .slice()
 * - Error Handling: Custom Error Class (TaskDataError), Type & Schema Validation, Try...Catch
 * - ES Module: export named & default
 */

// =============================================================================
// 1. KONSTANTA & ENUM
// =============================================================================

/**
 * Bobot prioritas untuk keperluan perbandingan dan pengurutan
 */
export const PRIORITY_WEIGHTS = Object.freeze({
    high: 3,
    medium: 2,
    low: 1
});

/**
 * Daftar status tugas yang diizinkan dalam sistem
 */
export const TASK_STATUSES = Object.freeze(['todo', 'inprogress', 'done', 'overdue']);

/**
 * Tingkat urgensi waktu tugas berdasarkan sisa jam menuju deadline
 */
export const URGENCY_LEVELS = Object.freeze({
    CRITICAL: 'critical', // < 24 jam
    WARNING: 'warning',   // 24 jam s.d. 72 jam (< 3 hari)
    SAFE: 'safe',         // >= 72 jam (>= 3 hari)
    OVERDUE: 'overdue',   // Lewat batas waktu
    DONE: 'done'          // Sudah selesai
});

// =============================================================================
// 2. ERROR HANDLING: KELAS ERROR KUSTOM
// =============================================================================

/**
 * Kelas error kustom untuk kesalahan validasi atau pemrosesan data tugas.
 * Mewarisi Error bawaan JavaScript agar jejak stack tetap terlacak.
 */
export class TaskDataError extends Error {
    /**
     * @param {string} message - Pesan deskripsi kesalahan
     * @param {string} [field=null] - Nama properti/field yang bermasalah jika ada
     * @param {*} [value=null] - Nilai yang menyebabkan kesalahan
     */
    constructor(message, field = null, value = null) {
        super(message);
        this.name = 'TaskDataError';
        this.field = field;
        this.value = value;
        this.timestamp = new Date().toISOString();
    }
}

// =============================================================================
// 3. ARROW FUNCTIONS: KALKULASI WAKTU & STATUS
// =============================================================================

/**
 * Menghitung selisih jam antara deadline tugas dengan waktu acuan.
 * Nilai negatif menandakan tugas telah melewati tenggat (overdue).
 *
 * @param {string|Date} deadlineStr - String tanggal deadline (ISO atau format valid)
 * @param {Date} [referenceDate=new Date()] - Waktu acuan perbandingan
 * @returns {number} Sisa jam (float, presisi 2 desimal)
 * @throws {TaskDataError} Jika deadlineStr tidak valid
 */
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

/**
 * Mengecek apakah sebuah deadline telah melewati waktu saat ini (overdue).
 *
 * @param {string|Date} deadlineStr
 * @param {Date} [referenceDate=new Date()]
 * @returns {boolean}
 */
export const isDeadlineOverdue = (deadlineStr, referenceDate = new Date()) => {
    try {
        return calculateRemainingHours(deadlineStr, referenceDate) < 0;
    } catch {
        return false;
    }
};

/**
 * Menentukan tingkat urgensi tugas berdasarkan sisa jam dan status tugas.
 *
 * @param {string|Date} deadlineStr - Tanggal deadline tugas
 * @param {string} status - Status tugas saat ini ('todo', 'inprogress', 'done', 'overdue')
 * @param {Date} [referenceDate=new Date()] - Waktu acuan
 * @returns {string} Salah satu nilai dari URGENCY_LEVELS
 */
export const getUrgencyLevel = (deadlineStr, status = 'todo', referenceDate = new Date()) => {
    if (status === 'done') {
        return URGENCY_LEVELS.DONE;
    }
    if (status === 'overdue') {
        return URGENCY_LEVELS.OVERDUE;
    }

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

// =============================================================================
// 4. VALIDASI DATA (FUNCTION & ARRAY METHOD: .every)
// =============================================================================

/**
 * Memvalidasi sebuah objek tugas tunggal.
 * Memeriksa keberadaan field wajib, tipe data, dan format deadline.
 *
 * @param {object} task - Objek tugas yang akan divalidasi
 * @returns {object} Objek tugas yang sudah disanitasi
 * @throws {TaskDataError} Jika ada data yang tidak memenuhi kriteria
 */
export function validateTask(task) {
    if (!task || typeof task !== 'object' || Array.isArray(task)) {
        throw new TaskDataError('Tugas harus berupa objek valid.', 'task', task);
    }

    // 1. Validasi ID
    if (!task.id || typeof task.id !== 'string' || !task.id.trim()) {
        throw new TaskDataError('ID tugas wajib berupa string tidak kosong.', 'id', task.id);
    }

    // 2. Validasi Judul
    if (!task.title || typeof task.title !== 'string' || !task.title.trim()) {
        throw new TaskDataError('Judul tugas (title) wajib diisi.', 'title', task.title);
    }

    // 3. Validasi Mata Kuliah
    if (!task.course || typeof task.course !== 'string' || !task.course.trim()) {
        throw new TaskDataError('Mata kuliah (course) wajib diisi.', 'course', task.course);
    }

    // 4. Validasi Deadline
    if (!task.deadline || typeof task.deadline !== 'string') {
        throw new TaskDataError('Deadline tugas wajib diisi string tanggal.', 'deadline', task.deadline);
    }
    const parsedDate = Date.parse(task.deadline);
    if (isNaN(parsedDate)) {
        throw new TaskDataError(`Format deadline "${task.deadline}" tidak valid.`, 'deadline', task.deadline);
    }

    // 5. Validasi Status
    const normalizedStatus = (task.status || 'todo').toLowerCase();
    if (!TASK_STATUSES.includes(normalizedStatus)) {
        throw new TaskDataError(
            `Status "${task.status}" tidak dikenal. Status yang valid: ${TASK_STATUSES.join(', ')}`,
            'status',
            task.status
        );
    }

    // 6. Validasi Prioritas
    const normalizedPriority = (task.priority || 'medium').toLowerCase();
    if (!Object.keys(PRIORITY_WEIGHTS).includes(normalizedPriority)) {
        throw new TaskDataError(
            `Prioritas "${task.priority}" tidak dikenal. Prioritas yang valid: high, medium, low`,
            'priority',
            task.priority
        );
    }

    // Return tugas yang sudah disanitasi
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

/**
 * Memvalidasi sekumpulan tugas sekaligus menggunakan method array `.every()`.
 * Memastikan setiap elemen adalah objek tugas yang memenuhi skema validasi.
 *
 * @param {Array<object>} tasks - Kumpulan objek tugas
 * @returns {boolean} True jika seluruh tugas valid
 * @throws {TaskDataError} Jika input bukan array atau ada tugas yang cacat
 */
export function validateTaskBatch(tasks) {
    if (!Array.isArray(tasks)) {
        throw new TaskDataError('Parameter tasks harus berupa Array.', 'tasks', tasks);
    }

    // Method array: .every()
    const allValid = tasks.every((task, index) => {
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

    return allValid;
}

// =============================================================================
// 5. PENYARINGAN & PENCARIAN (ARRAY METHOD: .filter)
// =============================================================================

/**
 * Memfilter daftar tugas berdasarkan kriteria dinamis.
 * Menggunakan method array `.filter()`.
 *
 * @param {Array<object>} tasks - Array of task objects
 * @param {object} [criteria={}] - Kriteria filter
 * @param {string} [criteria.course] - Filter mata kuliah tertentu (atau 'all')
 * @param {string} [criteria.status] - Filter status tertentu (atau 'all')
 * @param {string} [criteria.priority] - Filter prioritas ('high', 'medium', 'low', 'all')
 * @param {string} [criteria.search] - Pencarian teks bebas (title, course, instructions)
 * @param {boolean} [criteria.isOverdue] - Hanya ambil tugas overdue jika true
 * @param {boolean} [criteria.isUrgent] - Hanya ambil tugas mendekati deadline (<= 24 jam) jika true
 * @param {Date} [referenceDate=new Date()] - Waktu acuan
 * @returns {Array<object>} Array tugas hasil filter
 * @throws {TaskDataError} Jika tasks bukan Array
 */
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

    // Method array: .filter()
    return tasks.filter(task => {
        // 1. Filter Mata Kuliah
        if (course !== 'all' && task.course !== course) {
            return false;
        }

        // 2. Filter Status
        if (status !== 'all' && task.status !== status) {
            return false;
        }

        // 3. Filter Prioritas
        if (priority !== 'all' && (task.priority || 'medium').toLowerCase() !== priority.toLowerCase()) {
            return false;
        }

        // 4. Filter Overdue
        if (typeof isOverdue === 'boolean') {
            const taskIsOverdue = task.status === 'overdue' || isDeadlineOverdue(task.deadline, referenceDate);
            if (isOverdue !== taskIsOverdue) return false;
        }

        // 5. Filter Mendesak (Urgent <= 24 jam dan belum done)
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

        // 6. Filter Pencarian Teks Bebas
        if (query) {
            const titleMatch = (task.title || '').toLowerCase().includes(query);
            const courseMatch = (task.course || '').toLowerCase().includes(query);
            const instMatch = (task.instructions || '').toLowerCase().includes(query);
            if (!titleMatch && !courseMatch && !instMatch) {
                return false;
            }
        }

        return true;
    });
}

/**
 * Mengambil daftar tugas yang mendesak (deadline <= ambang batas jam dan belum selesai).
 * Menggunakan method array `.filter()`.
 *
 * @param {Array<object>} tasks - Array of task objects
 * @param {number} [thresholdHours=24] - Ambang batas jam (default 24 jam)
 * @param {Date} [referenceDate=new Date()] - Waktu acuan
 * @returns {Array<object>}
 */
export const getUrgentTasks = (tasks, thresholdHours = 24, referenceDate = new Date()) => {
    if (!Array.isArray(tasks)) {
        throw new TaskDataError('Parameter tasks harus berupa Array.', 'tasks', tasks);
    }
    // Method array: .filter()
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

/**
 * Mengambil daftar tugas yang sudah melewati deadline dan belum ditandai selesai.
 * Menggunakan method array `.filter()`.
 *
 * @param {Array<object>} tasks - Array of task objects
 * @param {Date} [referenceDate=new Date()]
 * @returns {Array<object>}
 */
export const getOverdueTasks = (tasks, referenceDate = new Date()) => {
    if (!Array.isArray(tasks)) {
        throw new TaskDataError('Parameter tasks harus berupa Array.', 'tasks', tasks);
    }
    // Method array: .filter()
    return tasks.filter(task => {
        if (task.status === 'done') return false;
        if (task.status === 'overdue') return true;
        return isDeadlineOverdue(task.deadline, referenceDate);
    });
};

// =============================================================================
// 6. TRANSFORMASI & FORMATTING DATA (ARRAY METHOD: .map)
// =============================================================================

/**
 * Memperkaya objek tugas dengan data komputasi (sisa jam, urgensi, status keterlambatan).
 * Menggunakan method array `.map()` untuk menjaga immutability dataset asal.
 *
 * @param {Array<object>} tasks - Array of task objects
 * @param {Date} [referenceDate=new Date()] - Waktu acuan
 * @returns {Array<object>} Array of enriched task objects
 */
export function enrichTasks(tasks, referenceDate = new Date()) {
    if (!Array.isArray(tasks)) {
        throw new TaskDataError('Parameter tasks harus berupa Array.', 'tasks', tasks);
    }

    // Method array: .map()
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

/**
 * Mendapatkan daftar nama mata kuliah unik dari kumpulan tugas, terurut alfabetis.
 * Menggunakan method array `.map()`, `.filter()`, dan Set.
 *
 * @param {Array<object>} tasks - Array of task objects
 * @returns {Array<string>} Array nama mata kuliah unik
 */
export const getUniqueCourses = (tasks) => {
    if (!Array.isArray(tasks)) return [];
    // Method array: .map() dan .filter()
    const allCourses = tasks
        .map(t => (t && typeof t.course === 'string' ? t.course.trim() : ''))
        .filter(course => course.length > 0);

    return Array.from(new Set(allCourses)).sort((a, b) => a.localeCompare(b));
};

// =============================================================================
// 7. PENCARIAN TUGAS TUNGGAL (ARRAY METHOD: .find, .findIndex)
// =============================================================================

/**
 * Mencari satu tugas berdasarkan ID uniknya.
 * Menggunakan method array `.find()`.
 *
 * @param {Array<object>} tasks - Array of task objects
 * @param {string} id - ID tugas yang dicari
 * @returns {object|null} Objek tugas jika ditemukan, null jika tidak ditemukan
 */
export const findTaskById = (tasks, id) => {
    if (!Array.isArray(tasks) || !id) return null;
    // Method array: .find()
    return tasks.find(task => task.id === id) || null;
};

/**
 * Mencari indeks posisi tugas berdasarkan ID.
 * Menggunakan method array `.findIndex()`.
 *
 * @param {Array<object>} tasks - Array of task objects
 * @param {string} id - ID tugas yang dicari
 * @returns {number} Indeks tugas (0..n) atau -1 jika tidak ditemukan
 */
export const findTaskIndex = (tasks, id) => {
    if (!Array.isArray(tasks) || !id) return -1;
    // Method array: .findIndex()
    return tasks.findIndex(task => task.id === id);
};

// =============================================================================
// 8. VERIFIKASI KEBERADAAN DATA (ARRAY METHOD: .some)
// =============================================================================

/**
 * Mengecek apakah ada setidaknya satu tugas aktif yang memiliki deadline kritis (<= thresholdHours) atau overdue.
 * Menggunakan method array `.some()`.
 *
 * @param {Array<object>} tasks - Array of task objects
 * @param {number} [thresholdHours=24] - Batas jam kritis
 * @param {Date} [referenceDate=new Date()] - Waktu acuan
 * @returns {boolean} True jika ada tugas mendesak/kritis
 */
export const hasCriticalDeadlines = (tasks, thresholdHours = 24, referenceDate = new Date()) => {
    if (!Array.isArray(tasks) || tasks.length === 0) return false;

    // Method array: .some()
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

// =============================================================================
// 9. PENGURUTAN DATA (ARRAY METHOD: .sort, .slice)
// =============================================================================

/**
 * Mengurutkan array tugas berdasarkan kriteria tertentu.
 * Menggunakan method array `.sort()` pada salinan array (non-mutating).
 *
 * @param {Array<object>} tasks - Array of task objects
 * @param {'deadline'|'priority'|'title'|'course'} [sortBy='deadline'] - Kriteria pengurutan
 * @param {'asc'|'desc'} [order='asc'] - Arah urutan
 * @returns {Array<object>} Salinan array tugas yang telah diurutkan
 */
export function sortTasks(tasks, sortBy = 'deadline', order = 'asc') {
    if (!Array.isArray(tasks)) {
        throw new TaskDataError('Parameter tasks harus berupa Array.', 'tasks', tasks);
    }

    const direction = order.toLowerCase() === 'desc' ? -1 : 1;
    const cloned = [...tasks];

    // Method array: .sort()
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

/**
 * Mengambil N tugas teratas yang paling mendesak, diurutkan berdasarkan deadline terdekat.
 * Menggunakan method array `.filter()`, `.sort()`, dan `.slice()`.
 *
 * @param {Array<object>} tasks - Array of task objects
 * @param {number} [limit=4] - Jumlah maksimal tugas yang diambil
 * @param {Date} [referenceDate=new Date()] - Waktu acuan
 * @returns {Array<object>}
 */
export const getTopUrgentTasks = (tasks, limit = 4, referenceDate = new Date()) => {
    if (!Array.isArray(tasks)) return [];

    const urgentTasks = getUrgentTasks(tasks, 24, referenceDate);
    const sorted = sortTasks(urgentTasks, 'deadline', 'asc');

    // Method array: .slice()
    return sorted.slice(0, Math.max(0, limit));
};

// =============================================================================
// 10. KALKULASI METRIK & PENGELOMPOKAN (ARRAY METHOD: .reduce)
// =============================================================================

/**
 * Menghitung ringkasan statistik menyeluruh dari sekumpulan tugas.
 * Menggunakan method array `.reduce()` untuk agregasi data dalam satu kali lintasan (single pass).
 *
 * @param {Array<object>} tasks - Array of task objects
 * @param {Date} [referenceDate=new Date()] - Waktu acuan
 * @returns {object} Objek metrik statistik:
 *   - total: total semua tugas
 *   - byStatus: jumlah tugas per status ('todo', 'inprogress', 'done', 'overdue')
 *   - byPriority: jumlah tugas per prioritas ('high', 'medium', 'low')
 *   - completedCount: jumlah tugas selesai
 *   - activeCount: jumlah tugas aktif (belum selesai)
 *   - overdueCount: jumlah tugas terlambat
 *   - urgentCount: jumlah tugas mendesak (< 24 jam)
 *   - completionRate: persentase penyelesaian (0 - 100%)
 */
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

    if (tasks.length === 0) {
        return initialStats;
    }

    // Method array: .reduce()
    const stats = tasks.reduce((acc, task) => {
        acc.total += 1;

        // Akumulasi Status
        const status = (task.status || 'todo').toLowerCase();
        if (acc.byStatus[status] !== undefined) {
            acc.byStatus[status] += 1;
        } else {
            acc.byStatus.todo += 1;
        }

        // Akumulasi Prioritas
        const priority = (task.priority || 'medium').toLowerCase();
        if (acc.byPriority[priority] !== undefined) {
            acc.byPriority[priority] += 1;
        } else {
            acc.byPriority.medium += 1;
        }

        // Hitung tugas selesai vs aktif
        if (status === 'done') {
            acc.completedCount += 1;
        } else {
            acc.activeCount += 1;

            // Periksa overdue & urgensi waktu untuk tugas non-done
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

    // Hitung persentase penyelesaian (completion rate)
    stats.completionRate = stats.total > 0
        ? Math.round((stats.completedCount / stats.total) * 1000) / 10
        : 0;

    return stats;
}

/**
 * Mengelompokkan tugas berdasarkan mata kuliah dan menghitung beban per mata kuliah.
 * Menggunakan method array `.reduce()` dan menghasilkan array of objects.
 *
 * @param {Array<object>} tasks - Array of task objects
 * @returns {Array<object>} Array of objects ringkasan per mata kuliah:
 *   [ { course: string, total: number, todo: number, inprogress: number, done: number, completionRate: number, tasks: Array<object> }, ... ]
 */
export function groupTasksByCourse(tasks) {
    if (!Array.isArray(tasks)) {
        throw new TaskDataError('Parameter tasks harus berupa Array.', 'tasks', tasks);
    }

    // Method array: .reduce() untuk memetakan ke objek penampung
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

    // Konversi objek map menjadi Array of Objects & hitung persentase tuntas
    return Object.values(groupedMap).map(item => ({
        ...item,
        completionRate: item.total > 0
            ? Math.round((item.done / item.total) * 1000) / 10
            : 0
    })).sort((a, b) => a.course.localeCompare(b.course));
}

/**
 * Mengelompokkan tugas berdasarkan tanggal batas waktu (YYYY-MM-DD) untuk tampilan kalender/agenda.
 * Menggunakan method array `.reduce()`.
 *
 * @param {Array<object>} tasks - Array of task objects
 * @returns {Record<string, Array<object>>} Objek dengan key tanggal (misal '2026-09-25') dan value array tugas
 */
export function groupTasksByDate(tasks) {
    if (!Array.isArray(tasks)) return {};

    // Method array: .reduce()
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

// =============================================================================
// 11. PARSER & SERIALISASI DATA DENGAN TRY...CATCH (ERROR HANDLING)
// =============================================================================

/**
 * Mengurai (parse) string JSON data tugas menjadi array of objects yang terverifikasi.
 * Melindungi aplikasi dari kegagalan parsing atau format data yang rusak menggunakan blok try...catch.
 *
 * @param {string} jsonString - String JSON dari localStorage atau file impor
 * @param {Array<object>} [fallbackTasks=[]] - Data cadangan jika parsing gagal
 * @returns {Array<object>} Array of valid task objects
 */
export function parseTasksFromJSON(jsonString, fallbackTasks = []) {
    if (!jsonString || typeof jsonString !== 'string') {
        return Array.isArray(fallbackTasks) ? fallbackTasks : [];
    }

    try {
        const parsed = JSON.parse(jsonString);

        if (!Array.isArray(parsed)) {
            console.warn('[TaskProcessor] Data hasil parse bukan merupakan Array. Menggunakan data cadangan.');
            return fallbackTasks;
        }

        // Sanitasi dan validasi setiap item yang valid
        const validatedTasks = parsed
            .map((item, idx) => {
                try {
                    return validateTask(item);
                } catch (itemErr) {
                    console.warn(`[TaskProcessor] Mengabaikan item cacat pada indeks ${idx}: ${itemErr.message}`);
                    return null;
                }
            })
            .filter(Boolean); // Hanya sisakan tugas yang berhasil divalidasi

        return validatedTasks.length > 0 ? validatedTasks : fallbackTasks;
    } catch (parseError) {
        console.error('[TaskProcessor] Gagal mem-parse JSON data tugas:', parseError.message);
        return fallbackTasks;
    }
}

/**
 * Mengonversi array of objects tugas menjadi string JSON yang terformat rapi.
 * Dilengkapi error handling jika data mengandung referensi sirkular atau tipe tidak valid.
 *
 * @param {Array<object>} tasks - Array of task objects
 * @returns {string} String JSON hasil serialisasi
 * @throws {TaskDataError} Jika serialisasi gagal
 */
export function exportTasksToJSON(tasks) {
    if (!Array.isArray(tasks)) {
        throw new TaskDataError('Data yang diekspor harus berupa Array.', 'tasks', tasks);
    }

    try {
        return JSON.stringify(tasks, null, 2);
    } catch (err) {
        throw new TaskDataError(`Gagal mengekspor tugas ke JSON: ${err.message}`, 'json', err);
    }
}

// =============================================================================
// 12. EXPORT DEFAULT (ES MODULE & WINDOW FALLBACK)
// =============================================================================

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

// Pasang ke objek window jika berjalan di lingkungan browser global non-modular
if (typeof window !== 'undefined') {
    window.TaskProcessor = taskProcessor;
}

export default taskProcessor;
