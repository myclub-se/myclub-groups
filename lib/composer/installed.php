<?php return array(
    'root' => array(
        'name' => 'myclub/myclub-groups',
        'pretty_version' => 'dev-main',
        'version' => 'dev-main',
        'reference' => '4227e3a8cc70ad909574cfaf8ca3e490e714111e',
        'type' => 'wordpress-plugin',
        'install_path' => __DIR__ . '/../../',
        'aliases' => array(),
        'dev' => true,
    ),
    'versions' => array(
        'myclub/common-lib' => array(
            'pretty_version' => '1.0.3',
            'version' => '1.0.3.0',
            'reference' => '305e4ef73395b6f6354229cc1571ffeb935e35bb',
            'type' => 'library',
            'install_path' => __DIR__ . '/../myclub/common-lib',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
        'myclub/myclub-groups' => array(
            'pretty_version' => 'dev-main',
            'version' => 'dev-main',
            'reference' => '4227e3a8cc70ad909574cfaf8ca3e490e714111e',
            'type' => 'wordpress-plugin',
            'install_path' => __DIR__ . '/../../',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
    ),
);
