<aside class="main-sidebar">

    <section class="sidebar">

        <?php
            $userMenus = \app\models\RefUserMenu::find()->where(['kd_user' => Yii::$app->user->identity->kd_user])->asArray()->all();
            $menus = \yii\helpers\arrayHelper::getColumn($userMenus, 'menu');
            // $menus = [];
        ?>
        <?= dmstr\widgets\Menu::widget(
            [
                'options' => ['class' => 'sidebar-menu', 'data-widget' => 'tree'],
                'items' => [
                    ['label' => 'Login', 'url' => ['site/login'], 'visible' => Yii::$app->user->isGuest],
                    ['label' => 'Dashboard', 'icon' => 'home', 'url' => ['/'],],
                    ['label' => 'Pengaturan', 'icon' => 'chevron-circle-right','url' => '#', 'visible' => 1, 'items'  =>
                        [
                            // ['label' => 'Pengaturan Global', 'icon' => 'circle-o', 'url' => ['/management/setting'], 'visible' => in_array(405, $menus)],
                            ['label' => 'User Management', 'icon' => 'circle-o', 'url' => ['/user/index'], 'visible' => in_array(102, $menus)],
                            ['label' => 'Akses Grup', 'icon' => 'circle-o', 'url' => ['/management/menu'], 'visible' => in_array(401, $menus)],
                            // ['label' => 'Mapping Komponen', 'icon' => 'circle-o', 'url' => ['/globalsetting/mappingkomponen'], 'visible' => in_array(104, $menus)],
                            // ['label' => 'Mapping Pendapatan', 'icon' => 'circle-o', 'url' => ['/globalsetting/mappingpendapatan'], 'visible' => in_array(105, $menus)],
                            // ['label' => 'Mapping Sisa Kas', 'icon' => 'circle-o', 'url' => ['/globalsetting/mappingsisa'], 'visible' => in_array(109, $menus)],
                            // ['label' => 'Blog/Pengumuman', 'icon' => 'circle-o', 'url' => ['/management/pengumuman'], 'visible' => in_array(106, $menus)],  
                            // ['label' => 'Seleksi Rekening', 'icon' => 'circle-o', 'url' => ['/globalsetting/selection'], 'visible' => in_array(107, $menus)], 
                            // ['label' => 'Program dan Kegiatan', 'icon' => 'circle-o', 'url' => ['/globalsetting/progker'], 'visible' => in_array(108, $menus)],                                                        
                            // ['label' => 'Komponen BOS', 'icon' => 'circle-o', 'url' => ['/parameter/komponen'], 'visible' => in_array(206, $menus)],
                            // ['label' => 'Potongan Belanja', 'icon' => 'circle-o', 'url' => ['/parameter/potongan'], 'visible' => in_array(207, $menus)],
                        ],
                    ],                    
                    // ['label' => 'Parameter', 'icon' => 'chevron-circle-right','url' => '#', 'visible' => 1,'items'  =>
                    //     [
                    //         ['label' => 'Sekolah', 'icon' => 'circle-o', 'url' => ['/parameter/sekolah'], 'visible' => in_array(201, $menus)],
                    //         ['label' => 'Data Sekolah', 'icon' => 'circle-o', 'url' => ['/parameter/datasekolah'], 'visible' => in_array(202, $menus)],
                    //         ['label' => 'Wilayah (Kec-Kel)', 'icon' => 'circle-o', 'url' => ['/parameter/wilayah'], 'visible' => in_array(204, $menus)],
                    //         ['label' => 'Aset Tetap', 'icon' => 'circle-o', 'url' => ['/parameter/rekening-aset-tetap'], 'visible' => in_array(205, $menus)],
                    //     ],
                    // ],                    
                    // // ['label' => 'Batch Process', 'icon' => 'circle-o', 'url' => ['/management/batchprocess'], 'visible' => in_array(301, $menus)],
                    // ['label' => 'Anggaran', 'icon' => 'chevron-circle-right', 'url' => '#', 'visible' => !Yii::$app->user->isGuest, 'items' => 
                    //     [
                    //         ['label' => 'RKAS', 'icon' => 'circle-o', 'url' => ['/anggaran/rkas'], 'visible' => in_array(402, $menus)],
                    //         ['label' => 'Anggaran Kas', 'icon' => 'circle-o', 'url' => ['/anggaran/rencana'], 'visible' => in_array(404, $menus)],
                    //         ['label' => 'Posting Anggaran', 'icon' => 'circle-o', 'url' => ['/anggaran/posting'], 'visible' => in_array(403, $menus)],
                    //         ['label' => 'Verifikasi Anggaran', 'icon' => 'circle-o', 'url' => ['/anggaran/baper'], 'visible' => in_array(406, $menus)],
                    //     ],
                    // ],
                    ['label' => 'Penatausahaan', 'icon' => 'chevron-circle-right', 'url' => '#', 'visible' => !Yii::$app->user->isGuest, 'items' => 
                        [
                            // ['label' => 'Saldo Awal', 'icon' => 'circle-o', 'url' => ['/penatausahaan/saldoawal'], 'visible' => in_array(507, $menus)],
                            // ['label' => 'Penerimaan', 'icon' => 'circle-o', 'url' => ['/penatausahaan/penerimaan'], 'visible' => in_array(501, $menus)],
                            // ['label' => 'Mutasi Kas', 'icon' => 'circle-o', 'url' => ['/penatausahaan/mutasikas'], 'visible' => in_array(508, $menus)],
                            // // ['label' => 'Belanja', 'icon' => 'circle-o', 'url' => ['/penatausahaan/belanja'], 'visible' => in_array(506, $menus)],
                            // ['label' => 'Belanja', 'icon' => 'circle-o', 'url' => ['/penatausahaan/bukti'], 'visible' => in_array(506, $menus)],
                            // ['label' => 'SPJ', 'icon' => 'circle-o', 'url' => ['/penatausahaan/spj'], 'visible' => in_array(502, $menus)],
                            // ['label' => 'Setoran Potongan', 'icon' => 'circle-o', 'url' => ['/penatausahaan/potongan'], 'visible' => in_array(509, $menus)],
                            // ['label' => 'Verifikasi SPJ', 'icon' => 'circle-o', 'url' => ['/penatausahaan/verspj'], 'visible' => in_array(503, $menus)],
                            // ['label' => 'Ubah Status SPJ', 'icon' => 'circle-o', 'url' => ['/penatausahaan/ubahstatusspj'], 'visible' => in_array(510, $menus)],
                            // ['label' => 'Penyesuaian setelah SPJ', 'icon' => 'circle-o', 'url' => ['/penatausahaan/koreksi'], 'visible' => in_array(511, $menus) || in_array(512, $menus), 'items'=> [
                            //     ['label' => 'Pendapatan', 'icon' => 'circle-o', 'url' => ['/penatausahaan/koreksi/pendapatan'], 'visible' => in_array(511, $menus)],
                            //     ['label' => 'Belanja', 'icon' => 'circle-o', 'url' => ['/penatausahaan/koreksi/belanja'], 'visible' => in_array(511, $menus)],
                            // ]],
                            ['label' => 'Cost Sheet/SPD', 'icon' => 'circle-o', /*'visible' => in_array(511, $menus) || in_array(512, $menus),*/ 'items'=> [
                                ['label' => 'Input Usulan', 'icon' => 'circle-o', 'url' => ['/spd/usulan'], 'visible' => in_array(501, $menus) ],
                                ['label' => 'Persetujuan Proglap', 'icon' => 'circle-o', 'url' => ['/spd/p3a'], 'visible' => in_array(502, $menus) ],
                                ['label' => 'Reviu Keuangan', 'icon' => 'circle-o', 'url' => ['/spd/reviukeu'], 'visible' => in_array(503, $menus) ],
                                ['label' => 'Reviu TU', 'icon' => 'circle-o', 'url' => ['/spd/reviutu'], 'visible' => in_array(504, $menus) ],
                                ['label' => 'Persetujuan Ka. Unit', 'icon' => 'circle-o', 'url' => ['/spd/kaunit'], 'visible' => in_array(505, $menus) ],
                                ['label' => 'Proses SPD', 'icon' => 'circle-o', 'url' => ['/spd/prosesspd'], 'visible' => in_array(506, $menus) ],
                                ['label' => 'Anggaran UM', 'icon' => 'circle-o', 'url' => ['/spd/anggaranum'], 'visible' => in_array(507, $menus) ],
                            ]],
                        ],
                    ], 
                    // ['label' => 'Aset Tetap', 'icon' => 'chevron-circle-right', 'url' => '#', 'visible' => !Yii::$app->user->isGuest, 'items' => 
                    //     [
                    //         ['label' => 'Inventarisasi', 'icon' => 'chevron-circle-right', 'url' => ['/asettetap/inventarisasi'], 'visible' => in_array(701, $menus), 'items' => [
                    //             ['label' => 'Tanah', 'icon' => 'circle-o', 'url' => ['/asettetap/inventarisasi/tanah'], 'visible' => in_array(701, $menus)],
                    //             ['label' => 'Peralatan dan Mesin', 'icon' => 'circle-o', 'url' => ['/asettetap/inventarisasi/peralatan-mesin'], 'visible' => in_array(701, $menus)],
                    //             ['label' => 'Gedung/Bangunan', 'icon' => 'circle-o', 'url' => ['/asettetap/inventarisasi/gedung'], 'visible' => in_array(701, $menus)],
                    //             ['label' => 'Jalan Jaringan dan Irigasi', 'icon' => 'circle-o', 'url' => ['/asettetap/inventarisasi/jji'], 'visible' => in_array(701, $menus)],
                    //             ['label' => 'Aset Tetap Lain', 'icon' => 'circle-o', 'url' => ['/asettetap/inventarisasi/atl'], 'visible' => in_array(701, $menus)],
                    //         ]],
                    //         ['label' => 'Kondisi', 'icon' => 'chevron-circle-right', 'url' => ['/asettetap/kondisi'], 'visible' => in_array(702, $menus), 'items' => [
                    //             ['label' => 'Tanah', 'icon' => 'circle-o', 'url' => ['/asettetap/kondisi/tanah'], 'visible' => in_array(702, $menus)],
                    //             ['label' => 'Peralatan dan Mesin', 'icon' => 'circle-o', 'url' => ['/asettetap/kondisi/peralatan-mesin'], 'visible' => in_array(702, $menus)],
                    //             ['label' => 'Gedung/Bangunan', 'icon' => 'circle-o', 'url' => ['/asettetap/kondisi/gedung'], 'visible' => in_array(702, $menus)],
                    //             ['label' => 'Jalan Jaringan dan Irigasi', 'icon' => 'circle-o', 'url' => ['/asettetap/kondisi/jji'], 'visible' => in_array(702, $menus)],
                    //             ['label' => 'Aset Tetap Lain', 'icon' => 'circle-o', 'url' => ['/asettetap/kondisi/atl'], 'visible' => in_array(702, $menus)],                        
                    //         ]],
                    //         ['label' => 'Penghapusan', 'icon' => 'chevron-circle-right', 'url' => ['/asettetap/hapus'], 'visible' => in_array(702, $menus), 'items' => [
                    //             ['label' => 'Tanah', 'icon' => 'circle-o', 'url' => ['/asettetap/hapus/tanah'], 'visible' => in_array(702, $menus)],
                    //             ['label' => 'Peralatan dan Mesin', 'icon' => 'circle-o', 'url' => ['/asettetap/hapus/peralatan-mesin'], 'visible' => in_array(702, $menus)],
                    //             ['label' => 'Gedung/Bangunan', 'icon' => 'circle-o', 'url' => ['/asettetap/hapus/gedung'], 'visible' => in_array(702, $menus)],
                    //             ['label' => 'Jalan Jaringan dan Irigasi', 'icon' => 'circle-o', 'url' => ['/asettetap/hapus/jji'], 'visible' => in_array(702, $menus)],
                    //             ['label' => 'Aset Tetap Lain', 'icon' => 'circle-o', 'url' => ['/asettetap/hapus/atl'], 'visible' => in_array(702, $menus)],                        
                    //         ]],
                    //         ['label' => 'Rekonsiliasi', 'icon' => 'circle-o', 'url' => ['/asettetap/rekon'], 'visible' => in_array(703, $menus)],
                    //     ],
                    // ],                   
                    // ['label' => 'Pelaporan', 'icon' => 'chevron-circle-right', 'url' => '#', 'visible' => !Yii::$app->user->isGuest, 'items' => 
                    //     [
                    //         ['label' => 'Pelaporan', 'icon' => 'circle-o', 'url' => ['/pelaporan/pelaporansekolah'], 'visible' => in_array(601, $menus)],
                    //         ['label' => 'Pelaporan', 'icon' => 'circle-o', 'url' => ['/pelaporan/pelaporanrekap'], 'visible' => in_array(602, $menus)],
                    //         ['label' => 'Pelaporan Sekolah', 'icon' => 'circle-o', 'url' => ['/pelaporan/pelaporanpantau'], 'visible' => in_array(606, $menus)],
                    //         ['label' => 'SP3B', 'icon' => 'circle-o', 'url' => ['/pelaporan/sp3b'], 'visible' => in_array(604, $menus)],
                    //         ['label' => 'SP2B', 'icon' => 'circle-o', 'url' => ['/pelaporan/sp2b'], 'visible' => in_array(605, $menus)],
                    //     ],
                    // ],
                    // ['label' => 'DB', 'icon' => 'chevron-circle-right', 'url' => '#', 'visible' => in_array(801, $menus), 'items' => 
                    //     [
                    //         ['label' => 'UPDATE DB', 'icon' => 'circle-o', 'url' => ['/update/db'], 'visible' => in_array(801, $menus)],
                    //         ['label' => 'Hapus DB', 'icon' => 'circle-o', 'url' => ['/update/delete'], 'visible' => in_array(801, $menus)],
                    //     ],
                    // ],
                    ['label' => 'Panduan Penggunaan', 'icon' => 'circle-o', 'url' => ['/images/bosstan_documentation_book.pdf'], 'visible' => !Yii::$app->user->isGuest, 'options' => ['onclick' => "return !window.open('".yii\helpers\Url::to(['/images/bosstan_documentation_book.pdf'], true)."', 'SPJ', 'width=1024,height=768')"]] 
                ],
            ]
        ) ?>

    </section>

</aside>
