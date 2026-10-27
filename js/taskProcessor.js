// 1. KONSTANTA & ENUM
// Objek ini fungsinya memetakan bobot angka prioritas untuk keperluan pengurutan (sorting)
export const PRIORITY_WEIGHTS = Object.freeze({
    high: 3,
    medium: 2,
    low: 1
});

// Array ini fungsinya mendefinisikan daftar status tugas yang valid dalam sistem
export const TASK_STATUSES = Object.freeze(['todo', 'inprogress', 'done', 'overdue']);

// Objek ini fungsinya sebagai konstanta label tingkat urgensi deadline tugas
export const URGENCY_LEVELS = Object.freeze({
    CRITICAL: 'critical',
    WARNING: 'warning',
    SAFE: 'safe',
    OVERDUE: 'overdue',
    DONE: 'done'
});

// 2. ERROR HANDLING: KELAS ERROR KUSTOM
// Kelas ini fungsinya membuat error kustom saat validasi atau pemrosesan data tugas gagal
export class TaskDataError extends Error {
    constructor(message, field = null, value = null) {
        super(message);
        this.name = 'TaskDataError';
        this.field = field; // Menyimpan nama field yang bermasalah
        this.value = value; // Menyimpan nilai yang memicu error
        this.timestamp = new Date().toISOString(); // Mencatat waktu terjadinya error
    }
}

// 3. KALKULASI WAKTU & STATUS
// Fungsi ini fungsinya menghitung selisih sisa jam antara deadline dengan waktu saat ini
export const calculateRemainingHours = (deadlineStr, referenceDate = new Date()) => {
    // Validasi: pastikan string deadline tidak kosong
    if (!deadlineStr) {
        throw new TaskDataError('Deadline tidak boleh kosong untuk kalkulasi waktu.', 'deadline', deadlineStr);
    }
    // Baris ini mengubah string deadline menjadi timestamp milidetik
    const deadlineTime = new Date(deadlineStr).getTime();
    if (isNaN(deadlineTime)) {
        throw new TaskDataError(`Format deadline "${deadlineStr}" tidak valid.`, 'deadline', deadlineStr);
    }
    // Mengambil timestamp dari waktu acuan pembanding
    const refTime = referenceDate instanceof Date ? referenceDate.getTime() : new Date(referenceDate).getTime();
    // Baris ini menghitung selisih jam (dibagi milidetik per jam)
    const diffHours = (deadlineTime - refTime) / (1000 * 60 * 60);
    // Membulatkan hasil kalkulasi selisih jam ke 2 angka desimal
    return Math.round(diffHours * 100) / 100;
};

// Fungsi ini fungsinya memeriksa apakah waktu deadline tugas sudah terlewat (menghasilkan boolean)
export const isDeadlineOverdue = (deadlineStr, referenceDate = new Date()) => {
    try {
        // Jika sisa jam bernilai negatif, berarti tugas sudah lewat tenggat
        return calculateRemainingHours(deadlineStr, referenceDate) < 0;
    } catch {
        return false;
    }
};

// Fungsi ini fungsinya menentukan kategori level urgensi tugas (overdue, critical, warning, safe, done)
export const getUrgencyLevel = (deadlineStr, status = 'todo', referenceDate = new Date()) => {
    // Jika tugas sudah selesai, set status urgensi menjadi done
    if (status === 'done') return URGENCY_LEVELS.DONE;
    // Jika status tugas memang overdue, langsung kembalikan level overdue
    if (status === 'overdue') return URGENCY_LEVELS.OVERDUE;

    try {
        const hours = calculateRemainingHours(deadlineStr, referenceDate);
        if (hours < 0) return URGENCY_LEVELS.OVERDUE; // Waktu tenggat terlewat
        if (hours <= 24) return URGENCY_LEVELS.CRITICAL; // Kritis: sisa waktu kurang dari 24 jam
        if (hours <= 72) return URGENCY_LEVELS.WARNING; // Peringatan: sisa waktu kurang dari 3 hari
        return URGENCY_LEVELS.SAFE; // Aman: sisa waktu lebih dari 3 hari
    } catch {
        return URGENCY_LEVELS.SAFE;
    }
};

// 4. SANITASI INPUT & VALIDASI FIELD CLIENT-SIDE
// Fungsi ini fungsinya membersihkan string dari tag HTML berbahaya untuk mencegah serangan XSS
export function sanitizeInput(str) {
    if (typeof str !== 'string') return '';
    return str
        .replace(/<[^>]*>/g, '') // Menghilangkan semua tag HTML
        .trim(); // Menghapus spasi berlebih di awal dan akhir
}

// Fungsi ini fungsinya memvalidasi judul tugas agar wajib diisi dan memiliki panjang 5-100 karakter
export function validateTaskTitle(title) {
    // Baris ini memeriksa apakah input judul kosong
    if (!title || typeof title !== 'string' || !title.trim()) {
        return 'Judul tugas wajib diisi.';
    }
    const clean = title.trim();
    // Validasi panjang karakter minimal
    if (clean.length < 5) {
        return `Judul tugas minimal 5 karakter (saat ini: ${clean.length} karakter).`;
    }
    // Validasi panjang karakter maksimal
    if (clean.length > 100) {
        return `Judul tugas maksimal 100 karakter (saat ini: ${clean.length} karakter).`;
    }
    return null; // Mengembalikan null jika validasi lolos
}

// Fungsi ini fungsinya memvalidasi nama mata kuliah agar terisi 3-60 karakter dan format karakter aman
export function validateTaskCourse(course) {
    // Baris ini memastikan nama mata kuliah tidak kosong
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
    // Baris ini memverifikasi karakter hanya huruf, angka, spasi, dan simbol umum
    const validPattern = /^[a-zA-Z0-9\s()\-.,&/]+$/;
    if (!validPattern.test(clean)) {
        return 'Nama mata kuliah hanya boleh memuat huruf, angka, spasi, dan tanda baca umum.';
    }
    return null;
}

// Fungsi ini fungsinya memvalidasi bahwa tenggat waktu terisi, format tanggal benar, dan tidak lampau untuk tugas baru
export function validateTaskDeadline(deadline, status = 'todo', isNew = false) {
    if (!deadline || typeof deadline !== 'string' || !deadline.trim()) {
        return 'Tenggat waktu (deadline) wajib diisi.';
    }
    const clean = deadline.trim();
    const parsedDate = Date.parse(clean);
    // Memastikan string tanggal dapat diparse oleh JavaScript
    if (isNaN(parsedDate)) {
        return 'Format tanggal tenggat waktu tidak valid.';
    }
    const deadlineTime = new Date(clean).getTime();
    const nowTime = Date.now();
    // Jika menambah tugas baru, cegah memasukkan tanggal yang sudah lewat
    if (isNew && deadlineTime < (nowTime - 60000)) {
        return 'Tenggat waktu untuk tugas baru tidak boleh berada di masa lampau.';
    }
    return null;
}

// Fungsi ini fungsinya memvalidasi bahwa opsi prioritas sesuai dengan High, Medium, atau Low
export function validateTaskPriority(priority) {
    const val = (priority || '').toLowerCase().trim();
    if (!Object.keys(PRIORITY_WEIGHTS).includes(val)) {
        return 'Prioritas harus salah satu dari: High, Medium, atau Low.';
    }
    return null;
}

// Fungsi ini fungsinya memvalidasi bahwa status tugas sesuai dengan 4 kolom Kanban yang didukung
export function validateTaskStatus(status) {
    const val = (status || '').toLowerCase().trim();
    if (!TASK_STATUSES.includes(val)) {
        return 'Status alur kerja harus salah satu dari: Planned, In progress, Completed, atau Overdue.';
    }
    return null;
}

// Fungsi ini fungsinya memvalidasi format link LMS agar berupa URL valid berawalan http:// atau https://
export function validateTaskUrl(url) {
    if (!url || typeof url !== 'string' || !url.trim()) {
        return null; // Tautan LMS bersifat opsional, kembalikan null jika kosong
    }
    const clean = url.trim();
    // Memeriksa keberadaan protokol web http atau https
    if (!/^https?:\/\//i.test(clean)) {
        return 'Tautan LMS harus diawali protokol http:// atau https:// (contoh: https://elearning.kampus.ac.id).';
    }
    try {
        const parsed = new URL(clean);
        // Memastikan hostname URL memiliki panjang minimal yang wajar
        if (!parsed.hostname || parsed.hostname.length < 3) {
            return 'Nama domain pada tautan LMS tidak valid.';
        }
    } catch {
        return 'Format tautan LMS tidak valid.';
    }
    return null;
}

// Fungsi ini fungsinya memvalidasi label tombol link LMS (maksimal 20 karakter & wajib ada URL jika label diisi)
export function validateTaskUrlLabel(label, url) {
    const cleanLabel = (label || '').trim();
    const cleanUrl = (url || '').trim();

    // Membatasi panjang label tombol
    if (cleanLabel.length > 20) {
        return `Label pintasan maksimal 20 karakter (saat ini: ${cleanLabel.length} karakter).`;
    }
    // Jika label diisi namun URL-nya kosong, kembalikan pesan error
    if (cleanLabel && !cleanUrl) {
        return 'Tautan LMS wajib diisi jika label pintasan ditentukan.';
    }
    return null;
}

// Fungsi ini fungsinya memvalidasi catatan instruksi tugas agar tidak melebihi batas 500 karakter
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

// Fungsi ini fungsinya memvalidasi seluruh data form tugas sekaligus dan mengembalikan status serta data yang bersih
export function validateTaskFormData(formData, options = { isNew: true }) {
    if (!formData || typeof formData !== 'object') {
        return {
            isValid: false,
            errors: { form: 'Data formulir tidak valid.' },
            sanitizedData: null
        };
    }

    const isNew = options && options.isNew === true;
    const errors = {}; // Objek untuk menampung pesan error setiap field

    // Baris-baris ini menjalankan validasi pada masing-masing field form
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

    // Menentukan apakah form valid (tidak ada error sama sekali)
    const isValid = Object.keys(errors).length === 0;

    // Menyusun objek data yang sudah disanitasi dari potensi skrip berbahaya
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

// Fungsi ini fungsinya memvalidasi objek tugas tunggal dan melemparkan TaskDataError jika ada kegagalan
export function validateTask(task) {
    if (!task || typeof task !== 'object' || Array.isArray(task)) {
        throw new TaskDataError('Tugas harus berupa objek valid.', 'task', task);
    }

    // Memastikan ID tugas ada dan bertipe string
    if (!task.id || typeof task.id !== 'string' || !task.id.trim()) {
        throw new TaskDataError('ID tugas wajib berupa string tidak kosong.', 'id', task.id);
    }

    const validationResult = validateTaskFormData(task, { isNew: false });
    if (!validationResult.isValid) {
        // Ambil field pertama yang mengalami error dan lempar sebagai error kustom
        const firstField = Object.keys(validationResult.errors)[0];
        const firstMsg = validationResult.errors[firstField];
        throw new TaskDataError(firstMsg, firstField, task[firstField]);
    }

    return {
        ...task,
        ...validationResult.sanitizedData
    };
}

// Fungsi ini fungsinya memvalidasi kumpulan tugas (batch array) sekaligus untuk memastikan integritas data
export function validateTaskBatch(tasks) {
    if (!Array.isArray(tasks)) {
        throw new TaskDataError('Parameter tasks harus berupa Array.', 'tasks', tasks);
    }

    // Iterasi setiap item array dan uji validitasnya
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
// Fungsi ini fungsinya menyaring array tugas berdasarkan kombinasi filter (matkul, status, prioritas, pencarian teks, dan tenggat)
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

    // Memfilter setiap item tugas dengan kondisi yang dipilih pengguna
    return tasks.filter(task => {
        // Cek kecocokan mata kuliah
        if (course !== 'all' && task.course !== course) return false;
        // Cek kecocokan status pengerjaan
        if (status !== 'all' && task.status !== status) return false;
        // Cek kecocokan prioritas (high, medium, low)
        if (priority !== 'all' && (task.priority || 'medium').toLowerCase() !== priority.toLowerCase()) return false;

        // Cek apakah filter tugas lewat tenggat diaktifkan
        if (typeof isOverdue === 'boolean') {
            const taskIsOverdue = task.status === 'overdue' || isDeadlineOverdue(task.deadline, referenceDate);
            if (isOverdue !== taskIsOverdue) return false;
        }

        // Cek apakah filter tugas mendesak (<24 jam) diaktifkan
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

        // Cek kecocokan kata kunci pencarian pada judul, matkul, atau petunjuk tugas
        if (query) {
            const titleMatch = (task.title || '').toLowerCase().includes(query);
            const courseMatch = (task.course || '').toLowerCase().includes(query);
            const instMatch = (task.instructions || '').toLowerCase().includes(query);
            if (!titleMatch && !courseMatch && !instMatch) return false;
        }

        return true;
    });
}

// Fungsi ini fungsinya mengambil daftar tugas yang belum selesai dan memiliki sisa waktu kurang dari threshold jam
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

// Fungsi ini fungsinya mengambil semua tugas yang telah melewati batas waktu pengumpulan dan belum berstatus selesai
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
// Fungsi ini fungsinya memperkaya setiap objek tugas dengan properti hitungan baru (sisa jam, urgensi, bobot prioritas)
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

        // Mengembalikan salinan tugas dengan metadata tambahan
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

// Fungsi ini fungsinya mengekstrak daftar unik nama mata kuliah dari seluruh tugas untuk opsi filter dropdown
export const getUniqueCourses = (tasks) => {
    if (!Array.isArray(tasks)) return [];
    const allCourses = tasks
        .map(t => (t && typeof t.course === 'string' ? t.course.trim() : ''))
        .filter(course => course.length > 0);

    // Menggunakan Set untuk memastikan tidak ada duplikasi nama mata kuliah
    return Array.from(new Set(allCourses)).sort((a, b) => a.localeCompare(b));
};

// 7. PENCARIAN TUGAS TUNGGAL
// Fungsi ini fungsinya mencari dan mengembalikan satu objek tugas yang cocok dengan ID tertentu
export const findTaskById = (tasks, id) => {
    if (!Array.isArray(tasks) || !id) return null;
    return tasks.find(task => task.id === id) || null;
};

// Fungsi ini fungsinya mencari indeks posisi suatu tugas dalam array berdasarkan ID-nya
export const findTaskIndex = (tasks, id) => {
    if (!Array.isArray(tasks) || !id) return -1;
    return tasks.findIndex(task => task.id === id);
};

// 8. VERIFIKASI KEBERADAAN DATA
// Fungsi ini fungsinya mengecek secara cepat apakah ada minimal satu tugas yang berada dalam status kritis/mendesak
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
// Fungsi ini fungsinya mengurutkan array tugas berdasarkan deadline, prioritas, judul, atau nama mata kuliah
export function sortTasks(tasks, sortBy = 'deadline', order = 'asc') {
    if (!Array.isArray(tasks)) {
        throw new TaskDataError('Parameter tasks harus berupa Array.', 'tasks', tasks);
    }

    // Arah pengurutan: asc (menaik) atau desc (menurun)
    const direction = order.toLowerCase() === 'desc' ? -1 : 1;
    const cloned = [...tasks];

    return cloned.sort((a, b) => {
        switch (sortBy) {
            case 'deadline': {
                // Pengurutan berdasarkan tanggal deadline
                const timeA = new Date(a.deadline).getTime() || 0;
                const timeB = new Date(b.deadline).getTime() || 0;
                return (timeA - timeB) * direction;
            }
            case 'priority': {
                // Pengurutan berdasarkan bobot prioritas
                const weightA = PRIORITY_WEIGHTS[(a.priority || 'medium').toLowerCase()] || 1;
                const weightB = PRIORITY_WEIGHTS[(b.priority || 'medium').toLowerCase()] || 1;
                return (weightA - weightB) * direction;
            }
            case 'title': {
                // Pengurutan abjad judul tugas
                const titleA = (a.title || '').toLowerCase();
                const titleB = (b.title || '').toLowerCase();
                return titleA.localeCompare(titleB) * direction;
            }
            case 'course': {
                // Pengurutan abjad mata kuliah
                const courseA = (a.course || '').toLowerCase();
                const courseB = (b.course || '').toLowerCase();
                return courseA.localeCompare(courseB) * direction;
            }
            default:
                return 0;
        }
    });
}

// Fungsi ini fungsinya mengambil beberapa tugas teratas yang paling mendesak (limit default 4 tugas)
export const getTopUrgentTasks = (tasks, limit = 4, referenceDate = new Date()) => {
    if (!Array.isArray(tasks)) return [];
    const urgentTasks = getUrgentTasks(tasks, 24, referenceDate);
    const sorted = sortTasks(urgentTasks, 'deadline', 'asc');
    return sorted.slice(0, Math.max(0, limit));
};

// 10. METRIK STATISTIK & AGREGASI
// Fungsi ini fungsinya menghitung ringkasan statistik komprehensif (jumlah per status, per prioritas, aktif, selesai, lewat tenggat, dan % kelulusan)
export function calculateTaskStatistics(tasks, referenceDate = new Date()) {
    if (!Array.isArray(tasks)) {
        throw new TaskDataError('Parameter tasks harus berupa Array.', 'tasks', tasks);
    }

    // Inisialisasi struktur data metrik awal
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

    // Baris ini melakukan akumulasi data menggunakan array reduce
    const stats = tasks.reduce((acc, task) => {
        acc.total += 1;

        // Hitung akumulasi per status tugas
        const status = (task.status || 'todo').toLowerCase();
        if (acc.byStatus[status] !== undefined) {
            acc.byStatus[status] += 1;
        } else {
            acc.byStatus.todo += 1;
        }

        // Hitung akumulasi per prioritas
        const priority = (task.priority || 'medium').toLowerCase();
        if (acc.byPriority[priority] !== undefined) {
            acc.byPriority[priority] += 1;
        } else {
            acc.byPriority.medium += 1;
        }

        // Hitung tugas yang telah selesai atau masih aktif
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

            // Jika status overdue atau deadline bernilai negatif
            if (status === 'overdue' || remainingHours < 0) {
                acc.overdueCount += 1;
            }

            // Jika sisa jam antara 0 hingga 24 jam (mendesak)
            if (remainingHours >= 0 && remainingHours <= 24) {
                acc.urgentCount += 1;
            }
        }

        return acc;
    }, initialStats);

    // Menghitung persentase progres penyelesaian (0 - 100%)
    stats.completionRate = stats.total > 0
        ? Math.round((stats.completedCount / stats.total) * 1000) / 10
        : 0;

    return stats;
}

// 11. PENGELOMPOKAN DATA
// Fungsi ini fungsinya mengelompokkan tugas berdasarkan nama mata kuliah lengkap dengan metrik progres tiap matkul
export function groupTasksByCourse(tasks) {
    if (!Array.isArray(tasks)) {
        throw new TaskDataError('Parameter tasks harus berupa Array.', 'tasks', tasks);
    }

    // Mengumpulkan dan mengelompokkan tugas ke dalam map berdasarkan mata kuliah
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

    // Menghitung persentase selesai per matkul dan mengurutkannya secara alfabetis
    return Object.values(groupedMap).map(item => ({
        ...item,
        completionRate: item.total > 0
            ? Math.round((item.done / item.total) * 1000) / 10
            : 0
    })).sort((a, b) => a.course.localeCompare(b.course));
}

// Fungsi ini fungsinya mengelompokkan tugas berdasarkan tanggal deadline (format YYYY-MM-DD) untuk tampilan kalender
export function groupTasksByDate(tasks) {
    if (!Array.isArray(tasks)) return {};

    return tasks.reduce((acc, task) => {
        if (!task.deadline) return acc;
        // Mengambil substring 10 karakter pertama (tahun-bulan-tanggal)
        const dateKey = task.deadline.slice(0, 10);
        if (!acc[dateKey]) {
            acc[dateKey] = [];
        }
        acc[dateKey].push(task);
        return acc;
    }, {});
}

// 12. PARSER & SERIALISASI JSON
// Fungsi ini fungsinya mem-parse string JSON data tugas dari penyimpanan, memvalidasi tiap item, dan menyediakan fallback jika korup
export function parseTasksFromJSON(jsonString, fallbackTasks = []) {
    if (!jsonString || typeof jsonString !== 'string') {
        return Array.isArray(fallbackTasks) ? fallbackTasks : [];
    }

    try {
        const parsed = JSON.parse(jsonString);

        // Jika hasil parse bukan array, kembalikan data fallback
        if (!Array.isArray(parsed)) {
            console.warn('[TaskProcessor] Data bukan Array, fallback digunakan.');
            return fallbackTasks;
        }

        // Memvalidasi setiap item tugas dan membuang data yang korup
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

// Fungsi ini fungsinya mengekspor array objek tugas menjadi format teks JSON yang terformat rapi
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
// Objek gabungan seluruh modul fungsi taskProcessor
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

// Menempelkan modul ke objek global window jika dijalankan di browser tanpa bundler
if (typeof window !== 'undefined') {
    window.TaskProcessor = taskProcessor;
}

// Export default untuk impor ES Module
export default taskProcessor;
