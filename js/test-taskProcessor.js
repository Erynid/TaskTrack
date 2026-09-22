/**
 * TaskTrack - Test Suite untuk js/taskProcessor.js
 * Memverifikasi:
 * 1. Array of Objects data structures
 * 2. Function & Arrow Function
 * 3. Array Methods (.filter, .map, .reduce, .find, .findIndex, .some, .every, .sort, .slice)
 * 4. Error Handling (TaskDataError, validation checks, try...catch parsing)
 * 5. ES Module import / export
 */

import taskProcessor, {
    TaskDataError,
    PRIORITY_WEIGHTS,
    TASK_STATUSES,
    URGENCY_LEVELS,
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
} from './taskProcessor.js';

let totalTests = 0;
let passedTests = 0;
let failedTests = 0;

function assert(condition, testName, extraInfo = '') {
    totalTests++;
    if (condition) {
        passedTests++;
        console.log(`  [PASS] ${testName}`);
    } else {
        failedTests++;
        console.error(`  [FAIL] ${testName} ${extraInfo ? '-> ' + extraInfo : ''}`);
    }
}

function assertThrows(fn, expectedErrorName, testName) {
    totalTests++;
    try {
        fn();
        failedTests++;
        console.error(`  [FAIL] ${testName} -> Seharusnya melempar error tapi tidak melempar.`);
    } catch (err) {
        if (!expectedErrorName || err.name === expectedErrorName || err instanceof TaskDataError) {
            passedTests++;
            console.log(`  [PASS] ${testName} (Tertangkap: ${err.name}: ${err.message})`);
        } else {
            failedTests++;
            console.error(`  [FAIL] ${testName} -> Tipe error tidak sesuai. Diharapkan: ${expectedErrorName}, didapat: ${err.name}`);
        }
    }
}

// Sample Data Array of Objects
const sampleTasks = [
    {
        id: 'task-1',
        title: 'Tugas Semantik HTML & Aksesibilitas Web',
        course: 'Pemrograman Web Dasar',
        deadline: '2026-09-25T16:00', // +3 hari
        status: 'todo',
        priority: 'high',
        lms_url: 'https://elearning.kampus.ac.id',
        lms_label: 'LMS',
        instructions: 'Pastikan struktur heading bertingkat dan kontras WCAG AA.'
    },
    {
        id: 'task-2',
        title: 'Desain Entity Relationship Diagram (ERD)',
        course: 'Sistem Basis Data',
        deadline: '2026-09-28T16:00', // +6 hari
        status: 'todo',
        priority: 'low',
        lms_url: 'https://classroom.google.com',
        lms_label: 'Classroom',
        instructions: 'Rancang ERD sistem rekam medis klinik.'
    },
    {
        id: 'task-3',
        title: 'Konfigurasi Routing OSPF & Subnetting',
        course: 'Jaringan Komputer Lanjut',
        deadline: '2026-09-24T16:00', // +2 hari
        status: 'inprogress',
        priority: 'medium',
        lms_url: 'https://elearning.kampus.ac.id',
        lms_label: 'LMS',
        instructions: 'Simulasikan di Cisco Packet Tracer.'
    },
    {
        id: 'task-4',
        title: 'Resume Materi Algoritma Dijkstra',
        course: 'Struktur Data & Algoritma',
        deadline: '2026-09-15T16:00', // -7 hari (lewat)
        status: 'done',
        priority: 'low',
        lms_url: 'https://elearning.kampus.ac.id',
        lms_label: 'LMS',
        instructions: 'Ringkas algoritma pencarian rute terpendek.'
    },
    {
        id: 'task-5',
        title: 'Upload Revisi Laporan Praktikum Modul 1',
        course: 'Pemrograman Berorientasi Objek',
        deadline: '2026-09-22T20:00', // +4 jam (mendesak)
        status: 'overdue',
        priority: 'high',
        lms_url: 'https://elearning.kampus.ac.id',
        lms_label: 'Kumpulkan Segera',
        instructions: 'Perbaiki diagram class dan screenshot unit test.'
    }
];

const mockNow = new Date('2026-09-22T16:00:00');

console.log('\n=== MEMULAI TEST SUITE: taskProcessor.js ===\n');

// -----------------------------------------------------------------------------
// TEST KELOMPOK 1: Validasi Objek & Error Handling
// -----------------------------------------------------------------------------
console.log('--- 1. Validasi Data & Error Handling (TaskDataError) ---');

assert(taskProcessor !== undefined, 'Default export taskProcessor tersedia');
assert(typeof calculateRemainingHours === 'function', 'Arrow function calculateRemainingHours terdefinisi');

// Validasi tugas benar
const validated1 = validateTask(sampleTasks[0]);
assert(validated1.title === sampleTasks[0].title, 'validateTask menerima objek valid');

// Error Handling: melempar TaskDataError jika bukan objek
assertThrows(() => validateTask('bukan objek'), 'TaskDataError', 'validateTask melempar error untuk non-object');

// Error Handling: melempar jika title kosong
assertThrows(() => validateTask({ id: 'x', title: '', course: 'Matkul', deadline: '2026-09-25T16:00' }), 'TaskDataError', 'validateTask melempar error saat judul kosong');

// Error Handling: melempar jika format deadline salah
assertThrows(() => validateTask({ id: 'x', title: 'Judul', course: 'Matkul', deadline: 'tanggal-salah' }), 'TaskDataError', 'validateTask melempar error saat deadline salah');

// Error Handling: melempar jika status tidak valid
assertThrows(() => validateTask({ id: 'x', title: 'Judul', course: 'Matkul', deadline: '2026-09-25T16:00', status: 'status_aneh' }), 'TaskDataError', 'validateTask melempar error saat status invalid');

// Validasi batch dengan .every()
assert(validateTaskBatch(sampleTasks) === true, 'validateTaskBatch (.every) menghasilkan true untuk sampleTasks');
assertThrows(() => validateTaskBatch([...sampleTasks, { id: 'bad' }]), 'TaskDataError', 'validateTaskBatch melempar error jika ada 1 elemen rusak');

// -----------------------------------------------------------------------------
// TEST KELOMPOK 2: Kalkulasi Waktu & Urgensi
// -----------------------------------------------------------------------------
console.log('\n--- 2. Kalkulasi Waktu & Urgensi (Arrow Functions) ---');

const hours1 = calculateRemainingHours('2026-09-22T20:00:00', mockNow);
assert(hours1 === 4, 'calculateRemainingHours menghitung selisih jam dengan akurat (4 jam)');

const hoursOverdue = calculateRemainingHours('2026-09-20T16:00:00', mockNow);
assert(hoursOverdue < 0, 'calculateRemainingHours menghasilkan nilai negatif untuk tanggal lampau');

assert(isDeadlineOverdue('2026-09-20T16:00:00', mockNow) === true, 'isDeadlineOverdue mengidentifikasi deadline lampau');
assert(isDeadlineOverdue('2026-09-25T16:00:00', mockNow) === false, 'isDeadlineOverdue mengidentifikasi deadline mendatang');

assert(getUrgencyLevel('2026-09-25T16:00', 'done', mockNow) === URGENCY_LEVELS.DONE, 'getUrgencyLevel mengembalikan "done" untuk tugas selesai');
assert(getUrgencyLevel('2026-09-22T20:00', 'todo', mockNow) === URGENCY_LEVELS.CRITICAL, 'getUrgencyLevel mengembalikan "critical" untuk < 24 jam');
assert(getUrgencyLevel('2026-09-24T16:00', 'todo', mockNow) === URGENCY_LEVELS.WARNING, 'getUrgencyLevel mengembalikan "warning" untuk 24-72 jam');
assert(getUrgencyLevel('2026-09-28T16:00', 'todo', mockNow) === URGENCY_LEVELS.SAFE, 'getUrgencyLevel mengembalikan "safe" untuk > 72 jam');

// -----------------------------------------------------------------------------
// TEST KELOMPOK 3: Array Method .filter
// -----------------------------------------------------------------------------
console.log('\n--- 3. Method Array: .filter() ---');

const filteredByCourse = filterTasks(sampleTasks, { course: 'Pemrograman Web Dasar' });
assert(filteredByCourse.length === 1 && filteredByCourse[0].id === 'task-1', 'filterTasks memfilter berdasarkan mata kuliah spesifik');

const filteredByStatus = filterTasks(sampleTasks, { status: 'todo' });
assert(filteredByStatus.length === 2, 'filterTasks memfilter berdasarkan status "todo" (2 tugas)');

const filteredByPriority = filterTasks(sampleTasks, { priority: 'high' });
assert(filteredByPriority.length === 2, 'filterTasks memfilter berdasarkan prioritas "high" (2 tugas)');

const filteredBySearch = filterTasks(sampleTasks, { search: 'dijkstra' });
assert(filteredBySearch.length === 1 && filteredBySearch[0].id === 'task-4', 'filterTasks mencari kata kunci judul/instruksi secara case-insensitive');

const urgentOnly = getUrgentTasks(sampleTasks, 24, mockNow);
assert(urgentOnly.length === 1 && urgentOnly[0].id === 'task-5', 'getUrgentTasks (.filter) mengambil tugas mendekati batas waktu');

// -----------------------------------------------------------------------------
// TEST KELOMPOK 4: Array Method .map
// -----------------------------------------------------------------------------
console.log('\n--- 4. Method Array: .map() ---');

const enriched = enrichTasks(sampleTasks, mockNow);
assert(enriched.length === sampleTasks.length, 'enrichTasks menghasilkan array dengan panjang sama');
assert(typeof enriched[0].remainingHours === 'number', 'enrichTasks menambahkan properti remainingHours');
assert(enriched[4].isUrgent === true, 'enrichTasks menandai isUrgent pada tugas mendekati deadline');

const uniqueCourses = getUniqueCourses(sampleTasks);
assert(uniqueCourses.length === 5, 'getUniqueCourses (.map + filter + Set) mendeteksi 5 mata kuliah unik terurut');
assert(uniqueCourses[0] === 'Jaringan Komputer Lanjut', 'getUniqueCourses mengurutkan alfabetis');

// -----------------------------------------------------------------------------
// TEST KELOMPOK 5: Array Method .find & .findIndex
// -----------------------------------------------------------------------------
console.log('\n--- 5. Method Array: .find() & .findIndex() ---');

const found = findTaskById(sampleTasks, 'task-3');
assert(found !== null && found.course === 'Jaringan Komputer Lanjut', 'findTaskById (.find) menemukan objek tugas berdasarkan ID');

const notFound = findTaskById(sampleTasks, 'task-tidak-ada');
assert(notFound === null, 'findTaskById mengembalikan null jika ID tidak ada');

const idx = findTaskIndex(sampleTasks, 'task-2');
assert(idx === 1, 'findTaskIndex (.findIndex) menemukan indeks yang tepat (1)');

// -----------------------------------------------------------------------------
// TEST KELOMPOK 6: Array Method .some
// -----------------------------------------------------------------------------
console.log('\n--- 6. Method Array: .some() ---');

const hasCritical = hasCriticalDeadlines(sampleTasks, 24, mockNow);
assert(hasCritical === true, 'hasCriticalDeadlines (.some) mendeteksi adanya tugas kritis');

const safeTasksOnly = [sampleTasks[0], sampleTasks[1]];
assert(hasCriticalDeadlines(safeTasksOnly, 24, mockNow) === false, 'hasCriticalDeadlines (.some) menghasilkan false jika semua aman');

// -----------------------------------------------------------------------------
// TEST KELOMPOK 7: Array Method .sort & .slice
// -----------------------------------------------------------------------------
console.log('\n--- 7. Method Array: .sort() & .slice() ---');

const sortedByDeadline = sortTasks(sampleTasks, 'deadline', 'asc');
assert(sortedByDeadline[0].id === 'task-4', 'sortTasks deadline asc: task-4 (tanggal paling lampau) berada di indeks 0');

const sortedByPriority = sortTasks(sampleTasks, 'priority', 'desc');
assert(sortedByPriority[0].priority === 'high', 'sortTasks priority desc: tugas high priority di urutan pertama');

const topUrgent = getTopUrgentTasks(sampleTasks, 2, mockNow);
assert(topUrgent.length <= 2, 'getTopUrgentTasks (.slice) membatasi jumlah hasil sesuai limit');

// -----------------------------------------------------------------------------
// TEST KELOMPOK 8: Array Method .reduce (Kalkulasi Statistik & Pengelompokan)
// -----------------------------------------------------------------------------
console.log('\n--- 8. Method Array: .reduce() ---');

const stats = calculateTaskStatistics(sampleTasks, mockNow);
assert(stats.total === 5, 'calculateTaskStatistics (.reduce) menghitung total tugas (5)');
assert(stats.completedCount === 1, 'calculateTaskStatistics menghitung tugas selesai (1)');
assert(stats.byStatus.todo === 2, 'calculateTaskStatistics menghitung status todo (2)');
assert(stats.byStatus.inprogress === 1, 'calculateTaskStatistics menghitung inprogress (1)');
assert(stats.completionRate === 20, 'calculateTaskStatistics menghitung completion rate (20.0%)');

const groupedCourses = groupTasksByCourse(sampleTasks);
assert(Array.isArray(groupedCourses), 'groupTasksByCourse (.reduce) menghasilkan Array of Objects');
assert(groupedCourses.length === 5, 'groupTasksByCourse menghasilkan 5 entri matkul');
assert(groupedCourses[0].total >= 1, 'groupTasksByCourse menghitung total tugas per matkul');

const groupedDates = groupTasksByDate(sampleTasks);
assert(groupedDates['2026-09-25'] !== undefined, 'groupTasksByDate (.reduce) mengelompokkan tugas berdasarkan tanggal');

// -----------------------------------------------------------------------------
// TEST KELOMPOK 9: Error Handling dengan Try...Catch (JSON Parser & Export)
// -----------------------------------------------------------------------------
console.log('\n--- 9. Error Handling: Try...Catch (JSON Parser & Serializer) ---');

const exportedJson = exportTasksToJSON(sampleTasks);
assert(typeof exportedJson === 'string' && exportedJson.includes('task-1'), 'exportTasksToJSON menghasilkan string JSON');

const parsedSuccess = parseTasksFromJSON(exportedJson);
assert(Array.isArray(parsedSuccess) && parsedSuccess.length === sampleTasks.length, 'parseTasksFromJSON mengurai JSON valid dengan sukses');

// Uji coba JSON rusak (Corrupted)
const corruptedJson = '{"invalid_json": true, broken';
const fallbackData = [{ id: 'fallback-1', title: 'Cadangan', course: 'Umum', deadline: '2026-09-30T10:00', status: 'todo', priority: 'low' }];
const parsedCorrupted = parseTasksFromJSON(corruptedJson, fallbackData);
assert(parsedCorrupted.length === 1 && parsedCorrupted[0].id === 'fallback-1', 'parseTasksFromJSON menangani JSON rusak dengan aman menggunakan try...catch dan mengembalikan fallback');

// -----------------------------------------------------------------------------
// HASIL AKHIR
// -----------------------------------------------------------------------------
console.log('\n=================================================');
console.log(`TOTAL PENGUJIAN : ${totalTests}`);
console.log(`LULUS (PASS)    : ${passedTests}`);
console.log(`GAGAL (FAIL)    : ${failedTests}`);
console.log('=================================================\n');

if (failedTests > 0) {
    process.exit(1);
} else {
    console.log('SEMUA PENGUJIAN BERHASIL 100% TANPA KESALAHAN!\n');
    process.exit(0);
}
