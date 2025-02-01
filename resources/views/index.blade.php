<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kiosk Video Upload</title>

    <!-- CDN Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- CDN Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <script>
        window.base_url = "{{ url('/') }}"; // Set base URL untuk API
    </script>
</head>
<body>
    <div class="container mx-auto p-4">
        <!-- Header Kiosk -->
        <header class="text-center mb-4">
            <h1 class="text-3xl font-bold">Welcome to Kiosk</h1>
        </header>

        <!-- Konten Utama -->
        <main>
            <!-- Form Upload Video -->
            <div class="max-w-4xl mx-auto bg-white p-6 rounded-lg shadow-lg">
                <h2 class="text-2xl font-semibold text-center mb-4">Upload Video ke Kiosk</h2>

                <!-- Menampilkan pesan sukses jika ada -->
                <div id="success-message" class="alert alert-success hidden"></div>
                <div id="error-message" class="alert alert-danger hidden"></div>

                <!-- Form Upload Video -->
                <form id="video-upload-form" class="space-y-4">
                    @csrf
                    <div class="form-group">
                        <label for="title" class="block text-lg">Judul Video</label>
                        <input type="text" name="title" id="title" class="form-control" required placeholder="Masukkan judul video">
                    </div>

                    <div class="form-group">
                        <label for="video" class="block text-lg">Pilih Video</label>
                        <input type="file" name="video" id="video" accept="video/mp4,video/x-m4v,video/*" class="form-control" required>
                    </div>

                    <button type="submit" id="submit-btn" class="btn btn-primary w-full py-2">Unggah Video</button>
                </form>
            </div>

            <!-- Daftar Video -->
            <div id="video-list" class="mt-8">
                <!-- Daftar Video yang sudah di-upload -->
                @foreach($videos as $video)
                    <div class="mb-6" id="video-{{ $video->id }}">
                        <div class="flex justify-between items-center">
                            <h4 class="text-lg font-semibold">{{ $video->title }}</h4>
                            <button class="btn btn-warning edit-btn" data-id="{{ $video->id }}">Edit</button>
                            <button class="btn btn-danger delete-btn" data-id="{{ $video->id }}">Hapus</button>
                        </div>
                        <video class="w-full mt-2" controls>
                            <source src="{{ asset('storage/' . $video->video_path) }}" type="video/mp4">
                            Your browser does not support the video tag.
                        </video>
                    </div>
                @endforeach
            </div>
        </main>

        <!-- Footer -->
        <footer class="text-center mt-4">
            <p>&copy; {{ date('Y') }} Kiosk Application</p>
        </footer>
    </div>

    <!-- JS dari Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- JavaScript untuk fungsionalitas -->
    <script>
        // Mengirim form menggunakan AJAX
        $('#video-upload-form').on('submit', function(e) {
            e.preventDefault();

            var formData = new FormData(this); // Ambil data dari form

            // Tampilkan tombol loading (opsional)
            $('#submit-btn').prop('disabled', true).text('Mengunggah...');

            $.ajax({
                url: window.base_url.replace("http://", "https://") + '/api/videos', // Force HTTPS
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    $('#submit-btn').prop('disabled', false).text('Unggah Video');
                    $('#success-message').removeClass('hidden').text(response.message);
                    $('#error-message').addClass('hidden');
                    $('#video-list').prepend(`
                        <div class="mb-6" id="video-${response.data.id}">
                            <div class="flex justify-between items-center">
                                <h4 class="text-lg font-semibold">${response.data.title}</h4>
                                <button class="btn btn-warning edit-btn" data-id="${response.data.id}">Edit</button>
                                <button class="btn btn-danger delete-btn" data-id="${response.data.id}">Hapus</button>
                            </div>
                            <video class="w-full mt-2" controls>
                                <source src="${response.data.video_path}" type="video/mp4">
                                Your browser does not support the video tag.
                            </video>
                        </div>
                    `);
                },
                error: function(xhr) {
                    $('#submit-btn').prop('disabled', false).text('Unggah Video');
                    $('#error-message').removeClass('hidden').text('Terjadi kesalahan, coba lagi.');
                    $('#success-message').addClass('hidden');
                }
            });
        });

        // Menambahkan event listener untuk tombol hapus
        $(document).on('click', '.delete-btn', function() {
            var videoId = $(this).data('id');
            deleteVideo(videoId);
        });

        // Fungsi untuk menghapus video
        function deleteVideo(videoId) {
            if (confirm('Yakin ingin menghapus video ini?')) {
                $.ajax({
                    url: window.base_url + `/api/videos/${videoId}`,
                    method: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        $('#video-' + videoId).remove();
                        alert(response.message);
                    },
                    error: function(xhr) {
                        alert('Terjadi kesalahan, coba lagi.');
                    }
                });
            }
        }

        // Menambahkan event listener untuk tombol edit
        $(document).on('click', '.edit-btn', function() {
            var videoId = $(this).data('id');
            editVideo(videoId);
        });

        // Fungsi untuk mengedit video
        function editVideo(videoId) {
            // Ambil data video dengan AJAX
            $.ajax({
                url: window.base_url + `/api/videos/${videoId}/edit`,
                method: 'GET',
                success: function(response) {
                    // Isi form dengan data video
                    $('#title').val(response.data.title);
                    // Set tombol submit untuk update
                    $('#video-upload-form').off('submit').on('submit', function(e) {
                        e.preventDefault();

                        var formData = new FormData(this);
                        $.ajax({
                            url: window.base_url + `/api/videos/${videoId}`,
                            method: 'PUT',
                            data: formData,
                            processData: false,
                            contentType: false,
                            success: function(response) {
                                alert(response.message);
                                location.reload(); // Refresh page setelah update
                            },
                            error: function(xhr) {
                                alert('Terjadi kesalahan, coba lagi.');
                            }
                        });
                    });
                },
                error: function(xhr) {
                    alert('Terjadi kesalahan, coba lagi.');
                }
            });
        }
    </script>
</body>
</html>
