<?php
/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, WordPress Language, and ABSPATH. You can find more information
 * by visiting {@link http://codex.wordpress.org/Editing_wp-config.php Editing
 * wp-config.php} Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'tameras1_wor2');

/** MySQL database username */
define('DB_USER', 'tameras1_wor2');

/** MySQL database password */
define('DB_PASSWORD', '4tEl951C');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'am8k(px/ZTI$C@z^9R{o+[ZiY>eRjTVV}!|Oif*xr=0(H!1P@4+|B@3d87+C4xRw');
define('SECURE_AUTH_KEY',  'Yi1}e=[H+A+ SOy|_$`$KstNj50x_3^-g|jY]4JJXf:vhK.7yaj)Rgmu.hNQ|ZNI');
define('LOGGED_IN_KEY',    'gO#,fp9RTnR?~@|Hvmi:#kJ|Sl>[SA >MW?wPxP*#l%5A.Y5K)S(WSw=aD<j=Ww=');
define('NONCE_KEY',        'B4F 025N~Fk5]n$K~hk2ZE+wcB0nS8r`|YO{*A_fQfDcO/v$3W{b^VfO-hXu6}yC');
define('AUTH_SALT',        'wBY$N#|i+@-+wq3vFfM#Gyi pDEZq^<lR@{p_(Y_X13pq+hK3M4}<3>w@;j|$t-5');
define('SECURE_AUTH_SALT', 'Cp#v;>Y{vJoNpqEU:zxv }fc$T#*GgG3/e:%v1f__hT=,/~A;QZ2S_@#!.wpGFWc');
define('LOGGED_IN_SALT',   'K(dNrk)X#2BYL bKLh67yC+Qi&UHK<AojVD;`X~t,`T+LX<u<T<_]+;95U!e-(&E');
define('NONCE_SALT',       'm`T!rmqaD~KejZx_SiF?sz@@4aKIg,<W/%s3D)_s5 wX)<7Q6T7=W>I6+b5u^s|-');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'ioj_';

/**
 * WordPress Localized Language, defaults to English.
 *
 * Change this to localize WordPress. A corresponding MO file for the chosen
 * language must be installed to wp-content/languages. For example, install
 * de_DE.mo to wp-content/languages and set WPLANG to 'de_DE' to enable German
 * language support.
 */
define('WPLANG', '');

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
