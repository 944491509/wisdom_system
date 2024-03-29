const mix = require('laravel-mix');

mix.js('resources/js/app.js', 'public/js')
    .js('resources/js/h5_apps/student.js', 'public/js/h5')
    .js('resources/js/h5_apps/teacher.js', 'public/js/h5')
    .sass('resources/sass/h5_apps/user.scss', 'public/css/h5')
    .sass('resources/sass/app.scss', 'public/css');

// mix.js('resources/js/smart/smart_basic.js', 'public/js')
//     .sass('resources/sass/smart/smart_basic.scss', 'public/css');

/*
 *   拷贝图片目录, js css 文件等
*/
mix.copyDirectory(
    'resources/sass/smart/fonts',
    'public/assets/fonts'
);
mix.copyDirectory(
    'resources/sass/smart/img',
    'public/assets/img'
);
mix.copyDirectory(
    'resources/sass/smart/plugins',
    'public/assets/plugins'
);
mix.copyDirectory(
    'resources/sass/smart/css',
    'public/assets/css'
);

mix.copyDirectory(
    'resources/js/smart/js',
    'public/assets/js'
);

mix.copy('resources/sass/smart/extra_page.css','public/css/extra_page.css');
mix.copy('resources/js/smart/extra_page.js','public/js/extra_page.js');

// 导入所见即所得编辑器 RedActor
mix.copyDirectory(
    'resources/redactor',
    'public/redactor'
);

mix.copyDirectory(
    'resources/sass/manual',
    'public/assets/manual'
);