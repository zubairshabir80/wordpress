<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'project' );

/** MySQL database username */
define( 'DB_USER', 'project' );

/** MySQL database password */
define( 'DB_PASSWORD', 'project' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'p&#>_VZPptIGnl]fB?p0AC1D#3^G>l^!9%dmS&YclOSn|GkTEY$AY=aVd#~T[hwK' );
define( 'SECURE_AUTH_KEY',  '^*#U!BF1D.bF}%JWfYLBJc(9]W|U(nw-+jZ%Y&R2:Y`_-/aXQ8Ue%Yt_`gjY/>^}' );
define( 'LOGGED_IN_KEY',    '7ndZ8B*!{R3k*n8qw{_-jmg9;01tUsju.]}jeZtx@r#SnynLFlXiE6EI_HVFEViA' );
define( 'NONCE_KEY',        'PQ?ksiJ/c4B{b0OrTlP7b;jFs}qdy7Y2}hbhS.LcpafB3~`oD|rU[0l!2|iVHQL/' );
define( 'AUTH_SALT',        'I&a(%wJw[-N5>,C8wSK*~#_A</@l8)zq?vxV@M=^^h4DJz8j!+O2FpM}1omM(M+$' );
define( 'SECURE_AUTH_SALT', 'r6_ch]>;ECw+m*iMTq1*_1h`zS?~YX#!!wUw7oLRTp_5R^Z,BS,|5bpQu]1S @zx' );
define( 'LOGGED_IN_SALT',   '@&*m9)Obf@UxM/L+:7X:nao<qssi*sC/5dAT!x&#=JeJu*M N8l9+XD|!hzV6>)z' );
define( 'NONCE_SALT',       '&3so.&IEs8]Vu%!%&h txw1X,MdGN~W,VI$WILf~;E{~,8@X`uKa<^w-u%(/l6qF' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
