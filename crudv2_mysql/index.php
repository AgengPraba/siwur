<?php
//error_reporting(0);
require_once 'core/harviacode.php';
require_once 'core/helper.php';
require_once 'core/process.php';
?>
<!doctype html>
<html>

<head>
    <title>Laravel CRUDv2 Mysql Generator</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" href="images/jenderal.jpeg" type="image/jpeg">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <style>
        body {
            padding: 15px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header text-white bg-primary mb-3">
                        Laravel CRUDv2 Mysql Bootstrap & Starter-Kit Livewire Laravel 12
                    </div>
                    <div class="card-body">
                        <button type="button" class="btn btn-primary btn-block" data-toggle="modal" data-target="#exampleModal">
                            Tutorial Install Sweetalert dan Datatables
                        </button>
                        <!-- Button trigger modal -->
                        <button type="button" class="btn btn-warning btn-block" data-toggle="modal" data-target="#installModal">
                            Tutorial Install Mary-UI Components & Livewire 3
                        </button>
                        
                        <hr>
                        <!-- Modal -->
                        <div class="modal fade" id="installModal" tabindex="-1" role="dialog" aria-labelledby="installModalLabel" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="installModalLabel">Mary-UI Installation & Overview</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <p>Ikuti langkah-langkah berikut untuk menginstall Mary-UI  dan Livewire 3 component:</p>
                                        <pre><code>composer require robsontenorio/mary

php artisan mary:install
          </code></pre>
                                        <p><strong>Apa itu Mary-UI Components?</strong></p>
                                        <p>Mary-UI adalah library komponen antarmuka pengguna untuk mempercepat pembuatan UI pada aplikasi Laravel dengan menyediakan komponen siap pakai yang mudah digunakan dan dikustomisasi.</p>
                                        <p>Untuk detail lengkap, kunjungi dokumentasi: <a href="https://mary-ui.com/docs/installation" target="_blank">https://mary-ui.com/docs/installation</a></p>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Modal -->
                        <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="tutorialModalLabel">Tutorial Install SweetAlert dan Datatables</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <h4>Langkah 1: Install SweetAlert</h4>
                                        <ol>
                                            <li>Buka terminal Anda dan jalankan perintah berikut untuk menginstal package SweetAlert:
                                                <pre><code>composer require realrashid/sweet-alert</code></pre>
                                            </li>
                                            <li>Tambahkan provider di file <code>config/providers.php</code>:
                                                <pre><code>RealRashid\SweetAlert\SweetAlertServiceProvider::class,</code></pre>
                                            </li>
                                            <li>Publikasikan aset dengan perintah:
                                                <pre><code>php artisan vendor:publish --provider="RealRashid\SweetAlert\SweetAlertServiceProvider"</code></pre>
                                            </li>

                                        </ol>

                                        <h4>Langkah 2: Install Datatables (Jika anda ingin menggunakan Datatables)</h4>
                                        <ol>
                                            <li>Install package Yajra Datatables:
                                                <pre><code>composer require yajra/laravel-datatables-oracle</code></pre>
                                            </li>
                                            <li>Tambahkan provider di file <code>config/providers.php</code> :
                                                <pre><code>Yajra\DataTables\DataTablesServiceProvider::class,</code></pre>
                                            </li>
                                            <li>Publikasikan konfigurasi dengan perintah:
                                                <pre><code>php artisan vendor:publish --provider="Yajra\DataTables\DataTablesServiceProvider"</code></pre>
                                            </li>

                                        </ol>
                                        <h4>Langkah 3: Install Livewire (Jika anda ingin menggunakan livewire)</h4>
                                        <ol>
                                            <li>Install Livewire menggunakan Composer:
                                                <pre><code>composer require livewire/livewire</code></pre>
                                            </li>
                                            <li>Publish Assets Livewire
                                                <pre><code>php artisan livewire:publish --config</code></pre>
                                            </li>

                                            <li>Buat komponen Livewire dengan perintah:
                                                <pre><code>php artisan make:livewire NamaKomponen</code></pre>
                                            </li>

                                        </ol>

                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <a class="btn btn-success btn-block" onclick="javascript: return confirm('Anda akan menimpa konfigurasi auth blade jika sudah ada, lanjutkan ?')" href="create_login_blade.php">Generate Auth Blade</a>
                        <a class="btn btn-info btn-block" onclick="javascript: return confirm('Anda harus sudah menginstall livewire, dan akan menimpa konfigurasi auth livewire jika sudah ada, lanjutkan ?')" href="create_login_livewire.php">Generate Auth Livewire Bootstrap</a>
                        <a class="btn btn-primary btn-block" onclick="javascript: return confirm('Anda harus sudah menginstall livewire, dan akan menimpa konfigurasi auth livewire jika sudah ada, lanjutkan ?')" href="create_login_livewire_mary.php">Generate Auth Livewire Mary-UI</a>
                        <hr>
                        <form action="index.php" method="POST" autocomplete="off">
                            <div class="form-group">
                                <label style="font-weight: bold">Pilih Tabel - <a
                                        href="<?php echo $_SERVER['PHP_SELF'] ?>">Refresh</a></label>
                                <select id="table_name" name="table_name" class="form-control select2"
                                    onchange="setname()">
                                    <option value="">Pilih Tabel</option>
                                    <?php
                                    $table_list = $hc->table_list();
                                    $table_list_selected = isset($_POST['table_name']) ? $_POST['table_name'] : '';
                                    foreach ($table_list as $table) {
                                        $pecah_tabel = explode('-', $table['table_name']);
                                    ?>
                                        <option value="<?php echo $table['table_name'] ?>" <?php echo $table_list_selected == $table['table_name'] ? 'selected="selected"' : ''; ?>>
                                            <?php echo $pecah_tabel[0] ?>
                                        </option>
                                    <?php
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label style="font-weight: bold">Pilih Teknologi Frontend</label>
                                <div class="row">
                                    <?php $jenis_frontend = isset($_POST['jenis_frontend']) ? $_POST['jenis_frontend'] : 'blade'; ?>
                                    <div class="col-md-4">
                                        <div class="radio" style="margin-bottom: 0px; margin-top: 0px">
                                            <label>
                                                <input type="radio" name="jenis_frontend" value="blade" <?php echo $jenis_frontend == 'blade' ? 'checked' : ''; ?>>
                                                Blade
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="radio" style="margin-bottom: 0px; margin-top: 0px">
                                            <label>
                                                <input type="radio" name="jenis_frontend" value="livewire" <?php echo $jenis_frontend == 'livewire' ? 'checked' : ''; ?>>
                                                Blade + Bootstrap + Livewire + SPA
                                            </label>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="radio" style="margin-bottom: 0px; margin-top: 0px">
                                            <label>
                                                <input type="radio" name="jenis_frontend" value="livewire_tailwind" <?php echo $jenis_frontend == 'livewire_tailwind' ? 'checked' : ''; ?>>
                                                Blade + Flux-ui + Livewire + SPA + Starter-Kit
                                            </label>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="radio" style="margin-bottom: 0px; margin-top: 0px">
                                            <label>
                                                <input type="radio" name="jenis_frontend" value="livewire_mary" <?php echo $jenis_frontend == 'livewire_mary' ? 'checked' : ''; ?>>
                                                Blade + Mary-UI + Livewire + SPA
                                            </label>
                                        </div>
                                    </div>

                                </div>
                            </div>
                            <div class="form-group">
                                <label style="font-weight: bold">Pilih Teknologi Penampilan data (Khusus Bootstrap)</label>
                                <div class="row">
                                    <?php $jenis_tabel = isset($_POST['jenis_tabel']) ? $_POST['jenis_tabel'] : 'datatables'; ?>
                                    <div class="col-md-4">
                                        <div class="radio" style="margin-bottom: 0px; margin-top: 0px">
                                            <label>
                                                <input type="radio" name="jenis_tabel" value="reguler_table" <?php echo $jenis_tabel == 'reguler_table' ? 'checked' : ''; ?>>
                                                Reguler Table
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="radio" style="margin-bottom: 0px; margin-top: 0px">
                                            <label>
                                                <input type="radio" name="jenis_tabel" value="datatables" <?php echo $jenis_tabel == 'datatables' ? 'checked' : ''; ?>>
                                                Serverside Datatables
                                            </label>
                                        </div>
                                    </div>

                                </div>
                            </div>
                            <div style="display:none;">

                                <div class="form-group">
                                    <label style="font-weight: bold"> Nama Controller</label>
                                    <input type="text" id="controller" name="controller"
                                        value="<?php echo isset($_POST['controller']) ? $_POST['controller'] : '' ?>"
                                        class="form-control" placeholder="Controller Name" />
                                </div>
                                <div class="form-group">
                                    <label style="font-weight: bold"> Nama Model</label>
                                    <input type="text" id="model" name="model"
                                        value="<?php echo isset($_POST['model']) ? $_POST['model'] : '' ?>"
                                        class="form-control" placeholder="Model Name" />
                                </div>
                                <div class="form-group">
                                    <label style="font-weight: bold"> View Form</label>
                                    <input type="text" id="form" name="form"
                                        value="<?php echo isset($_POST['form']) ? $_POST['form'] : '' ?>"
                                        class="form-control" placeholder="View Form Name" />
                                </div>
                                <div class="form-group">
                                    <label style="font-weight: bold"> View List</label>
                                    <input type="text" id="list" name="list"
                                        value="<?php echo isset($_POST['list']) ? $_POST['list'] : '' ?>"
                                        class="form-control" placeholder="View List Name" />
                                </div>
                                <div class="form-group">
                                    <label style="font-weight: bold"> View Show</label>
                                    <input type="text" id="show" name="show"
                                        value="<?php echo isset($_POST['show']) ? $_POST['show'] : '' ?>"
                                        class="form-control" placeholder="View Show Name" />
                                </div>
                            </div>





                            <input type="submit" value="Generate" name="generate" class="btn btn-danger btn-block"
                                onclick="javascript: return confirm('ini akan menimpa beberapa MVC anda, lanjutkan ?')" />
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header text-white bg-info mb-3">
                        Laporan Generator CRUD
                    </div>
                    <table class="table table-striped">
                        <tr>
                            <td>Tabel</td>
                            <td>
                                <?= $table_list_selected ?>
                            </td>
                        </tr>

                    </table>
                    <div class="card-body">
                        <?php
                        foreach ($hasil as $h) {
                            echo '<p>' . $h . '</p>';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            $('.select2').select2({
                closeOnSelect: true
            });
        });

        function capitalize(s) {
            return s && s[0].toUpperCase() + s.slice(1);
        }

        function setname() {
            var table_name = document.getElementById('table_name').value.toLowerCase();
            var modul_name = document.getElementById('modul_name').value;
            if (table_name != '') {
                document.getElementById('controller').value = capitalize(table_name) + 'Controller';
                document.getElementById('model').value = capitalize(table_name);
                document.getElementById('form').value = 'create' + capitalize(table_name) + '.blade';
                document.getElementById('list').value = 'index' + capitalize(table_name) + '.blade';
                document.getElementById('show').value = 'show' + capitalize(table_name) + '.blade';

            } else {
                document.getElementById('controller').value = '';
                document.getElementById('model').value = '';
                document.getElementById('form').value = '';
                document.getElementById('list').value = '';
                document.getElementById('show').value = '';
            }

            if (modul_name != '') {
                document.getElementById('modulnya').value = capitalize(modul_name);
            } else {
                document.getElementById('modulnya').value = '';
            }
            console.log('table_name => ' + table_name);
            console.log('modul_name => ' + modul_name);
        }
    </script>
</body>

</html>