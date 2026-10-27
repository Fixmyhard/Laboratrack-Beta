<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Kalender Kegiatan & Libur
        </h2>
    </x-slot>

    {{-- STYLE --}}
    <style>
        .dark .fc { color: #17191d; }
        .dark .fc-theme-standard th, .dark .fc-theme-standard td, .dark .fc-theme-standard .fc-scrollgrid { border-color: #374151; }
        .dark .fc-daygrid-day-number { color: #1c1d20; }
        .dark .fc-col-header-cell-cushion { color: #141516; }
        .fc .fc-button-primary { background-color: #4f46e5 !important; border-color: #4338ca !important; }
        .fc .fc-button-primary:hover { background-color: #4338ca !important; }
        .fc .fc-day-today { background-color: rgba(79, 70, 229, 0.1) !important; }
        .fc-header-toolbar { flex-wrap: wrap; gap: 0.5rem; }
        /* Warna judul toolbar putih di dark mode */
        .dark .fc-toolbar-title, .dark .fc-button { color: #ffffff !important; }

        /* PERBAIKAN: Paksa warna text event menjadi PUTIH dan TEBAL agar terlihat di background merah/biru */
        .fc-event-title {
            color: #ffffff !important;
            font-weight: 600;
            padding-left: 4px;
        }
    </style>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-200 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-900">
                    <div id="calendar" style="min-height: 600px;"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL TAMBAH/EDIT KEGIATAN --}}
    <div id="eventModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-gray-700 bg-opacity-75" aria-hidden="true" onclick="closeModal()"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            
            <div class="inline-block overflow-hidden text-left align-bottom transition-all transform bg-white dark:bg-gray-700 rounded-lg shadow-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="px-4 pt-5 pb-4 bg-white dark:bg-gray-700 sm:p-6 sm:pb-4">
                    <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100" id="modal-title">
                        Form Kegiatan
                    </h3>
                    <div class="mt-4">
                        {{-- HIDDEN INPUTS --}}
                        <input type="hidden" id="event_id"> 
                        <input type="hidden" id="start_date">
                        <input type="hidden" id="end_date">
                        
                        {{-- INFO: Input Nama Kegiatan DIHAPUS, akan diisi otomatis dari jenis --}}
                        
                        <div class="mb-4">
                            <label class="block text-sm font-bold mb-2 dark:text-gray-300">Jenis</label>
                            <select id="jenis" class="w-full border rounded px-3 py-2 dark:bg-gray-600 dark:text-white">
                                <option value="libur">Libur</option>
                                <option value="acara">Acara Kantor</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-bold mb-2 dark:text-gray-300">Catatan (Opsional)</label>
                            <textarea id="catatan" rows="3" class="w-full border rounded px-3 py-2 dark:bg-gray-600 dark:text-white" placeholder="Contoh: Libur Hari Natal"></textarea>
                        </div>
                    </div>
                </div>
                
                <div class="px-4 py-3 bg-gray-50 dark:bg-gray-600 sm:px-6 sm:flex sm:flex-row-reverse justify-between">
                    <div class="flex flex-row-reverse">
                        <button type="button" onclick="saveEvent()" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 sm:ml-3 sm:w-auto sm:text-sm">
                            Simpan
                        </button>
                        <button type="button" onclick="closeModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Batal
                        </button>
                    </div>

                    <button type="button" id="btnDelete" onclick="deleteEvent()" class="hidden mt-3 w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 sm:mt-0 sm:w-auto sm:text-sm">
                        Hapus
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- SCRIPT --}}
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            
            if (calendarEl) {
                var calendar = new FullCalendar.Calendar(calendarEl, {
                    initialView: 'dayGridMonth',
                    locale: 'id', 
                    selectable: true,
                    editable: true, 
                    height: 'auto', 
                    events: "{{ route('kalender.events') }}", 

                    // PERBAIKAN: Jika judul event kosong (data lama), tampilkan jenis kegiatannya
                    eventDidMount: function(info) {
                        if (!info.event.title && info.event.extendedProps.jenis) {
                            var titleEl = info.el.querySelector('.fc-event-title');
                            if (titleEl) {
                                // Capitalize first letter (misal: 'libur' -> 'Libur')
                                var text = info.event.extendedProps.jenis;
                                titleEl.textContent = text.charAt(0).toUpperCase() + text.slice(1);
                            }
                        }
                    },

                    // 1. KLIK TANGGAL KOSONG -> CREATE
                    select: function(info) {
                        // Reset Form
                        document.getElementById('event_id').value = ''; 
                        
                        // Default Jenis
                        document.getElementById('jenis').value = 'libur';
                        document.getElementById('catatan').value = '';
                        
                        document.getElementById('start_date').value = info.startStr;
                        document.getElementById('end_date').value = info.endStr;
                        
                        document.getElementById('modal-title').innerText = 'Tambah Kegiatan';
                        document.getElementById('btnDelete').classList.add('hidden'); 
                        document.getElementById('eventModal').classList.remove('hidden');
                    },

                    // 2. KLIK EVENT -> EDIT
                    eventClick: function(info) {
                        document.getElementById('event_id').value = info.event.id;
                        document.getElementById('start_date').value = info.event.startStr;
                        document.getElementById('end_date').value = info.event.endStr || info.event.startStr;
                        
                        var props = info.event.extendedProps;
                        document.getElementById('jenis').value = props.jenis;
                        document.getElementById('catatan').value = props.catatan || '';

                        document.getElementById('modal-title').innerText = 'Edit Kegiatan';
                        document.getElementById('btnDelete').classList.remove('hidden'); 
                        document.getElementById('eventModal').classList.remove('hidden');
                    },

                    eventDrop: function(info) {
                        updateEventDates(info);
                    }
                });
                
                calendar.render();
            }
        });

        function closeModal() {
            document.getElementById('eventModal').classList.add('hidden');
        }

        function saveEvent() {
            var id = document.getElementById('event_id').value;
            var start = document.getElementById('start_date').value;
            var end = document.getElementById('end_date').value;
            var catatan = document.getElementById('catatan').value;
            
            // Ambil elemen Select Jenis
            var jenisSelect = document.getElementById('jenis');
            var jenisVal = jenisSelect.value; // Contoh: 'libur' (untuk logika warna)
            var jenisText = jenisSelect.options[jenisSelect.selectedIndex].text; // Contoh: 'Libur' (untuk Judul Event)

            // Validasi sederhana
            if(!jenisVal) { alert('Jenis kegiatan harus dipilih!'); return; }

            var url = "{{ route('kalender.store') }}";
            var method = "POST";

            if (id) {
                url = "{{ route('kalender.update', ':id') }}".replace(':id', id);
                method = "PUT";
            }

            fetch(url, {
                method: method,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    // PERBAIKAN: Kirim Text Jenis sebagai 'kegiatan' agar muncul sebagai judul di kalender
                    kegiatan: jenisText, 
                    waktu_mulai: start,
                    waktu_berakhir: end,
                    jenis_kegiatan: jenisVal,
                    catatan: catatan
                })
            })
            .then(async response => {
                if (!response.ok) {
                    const data = await response.json();
                    throw new Error(data.message || 'Terjadi kesalahan');
                }
                return response.json();
            })
            .then(data => {
                alert(data.message || 'Berhasil disimpan');
                location.reload(); 
            })
            .catch(error => {
                alert(error.message); 
            });
        }

        function deleteEvent() {
            var id = document.getElementById('event_id').value;
            if(!id) return;

            if(confirm("Yakin ingin menghapus kegiatan ini?")) {
                var url = "{{ route('kalender.destroy', ':id') }}".replace(':id', id);

                fetch(url, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    location.reload();
                });
            }
        }

        function updateEventDates(info) {
            var id = info.event.id;
            var url = "{{ route('kalender.update', ':id') }}".replace(':id', id);
            
            fetch(url, {
                method: 'PUT',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    waktu_mulai: info.event.startStr,
                    waktu_berakhir: info.event.endStr
       
                })
            })
            .then(response => response.json())
            .then(data => console.log('Event moved'));
        }
    </script>
</x-app-layout>