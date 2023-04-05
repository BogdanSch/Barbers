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
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'gbua_barbers_bs' );

/** Database username */
define( 'DB_USER', 'gbua_barbers_bs' );

/** Database password */
define( 'DB_PASSWORD', 'af6z5f9a59a' );

/** Database hostname */
define( 'DB_HOST', 'mysql317.1gb.ua' );

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
define( 'AUTH_KEY',         'Ql aB#a*AOlU5W@heW<8#0`xIeB6>]{<*#[*`i5dN}&|Mo,.fg!rjg8~kc6^mLLk' );
define( 'SECURE_AUTH_KEY',  '#FpdoIfGd>2CLnL=mSAS7OP=%t$p;<B9Dzp4Pa~p/(DN9b=sV#9CbddbA2;D#-4<' );
define( 'LOGGED_IN_KEY',    'auW$^#CmUoQo2+z:Y[CAh2fKFMpFGKSl<%aOUCFQRnc^LpMh^O{!0N)&xkP[o;{D' );
define( 'NONCE_KEY',        'dOUv[yK}wm,mwA7ov~TaoMhWQ,;u*x8t]CB;TV@C1Z.jHtSn&mL&<Y^M#8{p+Px-' );
define( 'AUTH_SALT',        '2JYZIm0>o{`UkbrpLx{I?B@Cj{y:noz-;@^)eDPDa1&5z<s?~P>uzGA?H,N9(]gL' );
define( 'SECURE_AUTH_SALT', 'bYx4%>l!rUGO%4~ScVKTtw8-9?62xOFuH~tnm4].}oyeeC0e+n`kx}4s}i|07,_1' );
define( 'LOGGED_IN_SALT',   '&ZRG@jx|]GDf*JV4W8Y|x:X9RiMhSSyfBcBfD#aiRU|<OQ# 9,D5OEwjO>A/0z3I' );
define( 'NONCE_SALT',       'LBS- I+9[5o+{_@E1Ox[_h)H}4H<g]|P^k~AIbaHdADVy~*q{4KrGgoG@jzh:=*9' );

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
