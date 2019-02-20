<?php 
namespace App\Traits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

trait message
{
    public static function responseGetSuccess($data)
    {
        $response = [
            'code' => 200,
            'status' => true,
            'data' => $data
        ];
        return response()->json($response, $response['code']);
    }

    public static function responseGetFailed()
    {
        $response = [
            'code' => 500,
            'status' => false,
        ];
        return response()->json($response, $response['code']);
    }

    public static function responseManageSuccess()
    {
        $response = [
            'code' => 200,
            'status' => true,
        ];
        return response()->json($response, $response['code']);
    }

    public static function responseManageFailed()
    {
        $response = [
            'code' => 500,
            'status' => false,
        ];
        return response()->json($response, $response['code']);
    }

    public static function createdResponse($data)
    {
        $response = [
        'code' => 201,
        'status' => 'succcess',
        'data' => $data
        ];
        return response()->json($response, $response['code']);
    }
    public static function showvalidation($data)
    {
        $data = json_decode($data);
        $error = array();
        foreach($data as $key => $d){
            array_push($error, $key);
        }

        $response = [
        'code' => 401,
        'message' => 'form not completed !',
        'data' => $error
        ];
        return response()->json($response, $response['code']);
    }
    public static function showResponse($data)
    {
        $response = [
        'code' => 200,
        'status' => 'succcess',
        'message' => 'Data Berhasil Ditampilkan',
        'data' => $data
        ];
        return response()->json($response, $response['code']);
    }
    public static function showResponseWithRow($data)
    {
        $response = [
        'code' => 200,
        'status' => 'succcess',
        'message' => 'Data Berhasil Ditampilkan',
        'data' => [
            'totalrow' => count($data),
            'data' => $data
        ]];
        return response()->json($response, $response['code']);
    }
    public static function notFoundResponse()
    {
        $response = [
        'code' => 200,
        'status' => 'error',
        'data' => '',
        'message' => 'Not Found'
        ];
        return response()->json($response, $response['code']);
    }
    public static function PK_error()
    {
        $response = [
        'code' => 422,
        'status' => 'error',
        'data' => '',
        'message' => 'Primary Key Error, cek Sequence Table atau index !'
        ];
        return response()->json($response, $response['code']);
    }
    public static function connection_timeout()
    {
        $response = [
        'code' => 504,
        'status' => 'error',
        'data' => '',
        'message' => 'Not Found'
        ];
        return response()->json($response, $response['code']);
    }
    public static function deletedResponse()
    {
        $response = [
        'code' => 204,
        'status' => 'success',
        'message' => 'Resource deleted'
        ];
        return response()->json($response, $response['code']);
    }
    public static function deletedResponseFailed()
    {
        $response = [
        'code' => 400,
        'status' => 'error',
        'data' => [],
        'message' => 'delete error'
        ];
        return response()->json($response, $response['code']);
    }

    public static function clientErrorResponse($data)
    {
        $response = [
        'code' => 422,
        'status' => 'error',
        'data' => $data,
        'message' => 'Unprocessable entity'
        ];
        return response()->json($response, $response['code']);
    }
    
    public static function loginSuccessResponse($data)
    {
        $response = [
        'code' => 200,
        'data' => $data,
        'message' => 'Login Success'
        ];
        return $response;
    }
    public static function loginFailResponse($key)
    {
        $code = $key == 1 ? 400 : 401;
        $message = $key == 1 ? 'Login Failed' : 'You have been logged in';
        $response = [
        'code' => $code,
        'message' => $message
        ];
        return $response;
    }
    public static function logoutResponse($key)
    {
        $code = $key == 1 ? 200 : 401;
        $message = $key == 1 ? 'Logout Success' : 'You have not logged';
        $response = [
        'code' => $code,
        'message' => $message
        ];
        return $response;
    }

    public static function uploadSuccessResponse($data)
    {
        $response = [
        'code' => 200,
        'data' => $data,
        'message' => 'upload success'
        ];
        return $response;
    }
    public static function uploadFailResponse()
    {
        $response = [
        'code' => 400,
        'message' => 'upload failed'
        ];
        return $response;
    }

    public static function findWorkPlaceResponse($key,$data){
        $code = $key == 1 ? 200 : 400;
        $message = $key == 1 ? 'Location is found' : 'Location is not found';
        $response = [
        'code' => $code,
        'data' => $data,
        'message' => $message
        ];
        return $response;
    }

    public static function isCheckedInSuccessResponse($key,$data){
        $message = $key == 1 ? "You have been checked in" : "You have not been checked in";
        $response = [
        'code' => 200,
        'data' => $data,
        'message' => $message
        ];
        return $response;
    }

    public static function isBreakInSuccessResponse($key,$data){
        $message = $key == 1 ? "You have been Break in" : "You have not been Break in";
        $response = [
            'code' => 200,
            'data' => $data,
            'message' => $message
        ];
        return $response;
    }

    public static function isBreakOutSuccessResponse($key,$data){
        $message = $key == 1 ? "You have been Break out" : "You have not been Break out";
        $response = [
            'code' => 200,
            'data' => $data,
            'message' => $message
        ];
        return $response;
    }

    public static function isRoleAttendenceSuccessResponse($key,$data){
        $message = $key == 1 ? "Checkin available in ".$data." minutes." : "Checkout available in".$data." minutes.";
        $response = [
            'code' => 200,
            'data' => $data,
            'message' => $message
        ];
        return $response;
    }

    public static function isRoleAttendenceFailedResponse($key){
        $message = $key == 1 ? "Checkin not available." : "Checkout not available.";
        $response = [
            'code' => 400,
            'message' => $message
        ];
        return $response;
    }

    public static function isCheckedInFailResponse()
    {
        $response = [
        'code' => 400,
        'message' => 'Check failed'
        ];
        return $response;
    }

    public static function isEmployee($key){
        $message = $key == 1 ? "is Employee." : "Not Employee.";
        $response = [
            'code' => 200,
            'message' => $message
        ];
        return $response;
    }

    public static function GetShowRowData($data, $request ){
        $page= $request->get('page') != '' ? (int)$request->get('page') : 1;
        $rows= $request->get('rows') != '' ? (int)$request->get('rows') : 10;

        $start = ($page>1) ? $page : 0;
        $total = $data->count();
        $pages = ceil($total/$rows);    
        $data->offset($start);
        $data->limit($rows);
        $no = $start+1;
        $grid = $data->get();
        

        if(DB::connection()->getPdo()){
            return response()->json(array('code' => 200, 
                                          'status' => 'success', 
                                          'message' => 'Data Berhasil Ditampilkan', 
                                          'totalRow' => $total, 
                                          'totalPages' => $pages, 
                                          'data' => $grid), 200);
        } else {
            return response()->json(array('code' => 501, 'status' => 'error', 'message' => 'Koneksi Gagal !'), 501);
        }
    }
}
/*

1. Error 400 (Bad Request)
Kode kesalahan 400 (Error Bad Request) sering ditemukan ketika kita salah mengetikkan alamat sebuah situs, coba periksa kembali pada adrress bar browser Anda untuk mengecek benar atau salah alamat situs tersbut. Atau kemungkinan lain server situs sedang mengalami gangguan, sehingga tidak dapat mengenali permintaan komputer Anda.

2. Error 401 (Server Nauthorized)
Anda yang tidak memiliki hak akses untuk membuka folder atau membuka website tersebut karena terproteksi oleh password.

3. Error 402 (Payment Required Error)
Tujuannya adalah bahwa kode ini dapat digunakan sebagai bagian dari beberapa bentuk digital cash atau skema micropayment, tapi hal itu tidak terjadi, dan kode ini jarang terjadi.

4. Error 403 (Server Forbidden)
Anda tidak bisa mengakses website tersebut, karena ini tidak menggunakan password melainkan kesalahan dalam konfigurasi folder oleh si Admin web tersebut.

5. Error 404 (Server Not Found)
Halaman website yang Anda minta tidak tersedia di web hosting, jadi Anda harus menunggu Admin web tersebut untuk memperbaikinya. Dengan cara file dan folder website harus di upload seluruhnya ke akun hosting dan alamat link harus disesuaikan, dan perlu Anda perhatikan bahwa setiap link bersifat sensitif terhadap huruf besar dan huruf kecil.

6. Error 405 (Methode Not Allowed)
Pesan ini muncul jika koneksi menggunakan metode yang tidak didukung oleh komputer server.

7. Error 406 (Not Acceptable)
Permintaan dari browser tidak dapat dipenuhi oleh server.

8. Error 407 (Proxy Authentication Required)
Klien harus terlebih dahulu mengotentikasi dirinya dengan proxy.

9. Error 408 (Requst Timeout)
Pesan error seperti ini biasa terjadi ketika koneksi jaringan internet kita lambat, sehingga komputer server memutus request karena terlalu lama. Untuk mengatasinya, refresh browser Anda. Jika masih sama, maka reset modem dan koneksikan ulang.

10. Error 409 (Conflict)
Pesan error ini muncul ketika kita sedang mengakses sebuah situs, namun pada saat yang bersamaan pemilik situs sedang mengedit halaman sehingga terjadi konflik, dan tidak dapat diproses oleh komputer server. Anda harus menunggu si Admin untuk menyelesaikan pengeditannya, dan setelah itu akan kembali normal lagi.

11. Error 410 (Gone)
Pesan kesalahan ini berarti halaman yang diminta sudah dihapus secara permanen, atau domain website tersebut sudah expired.

12. Error 411 (Length Required)
Permintaan tidak sesuai dengan panjang isi yang diperlukan oleh sumber daya yang diminta.

13. Error 412 (Precondition Failed)
Server tidak memenuhi salah satu prasyarat bahwa pemohon memakai permintaan tersebut.

14. Error 413 (Request Entity Too Large)
Jika Anda menemukan kode kesalahan seperti ini artinya, data yang diminta terlalu besar dan komputer server tidak bisa menampung besarnya data yang Anda minta.

15. Error 414 (Reques URL Too Long)
Pesan error seperti ini sudah jarang dijumpai karena URL sudah dibuat sedemikian rupa lebih singkat. Kode error ini berarti URL yang ingin dituju terlalu panjang dan server tak mampu memprosesnya.

16. Error 415 (Unsupported Media Type)
Error ini akan muncul jika kita menggunakan jenis media yang tidak didukung atau tidak diizinkan oleh server. Misalnya kita mengunggah file gambar dengan format .PNG, namun server hanya mengizinkan format .JPG atau .GIF. Umumnya server tidak akan menampilkan status ini pada browser kita saat kondisi tersebut terjadi, melainkan hanya menampilkan pemberitahuan bahwa file gambar yang kita unggah tersebut tidak sesuai.

17. Error 416 (Requested Range Not Satisfiable)
Klien telah meminta untuk sebagian dari file, tetapi server tidak dapat menyediakan bagian itu. Sebagai contoh, jika klien meminta bagian dari file yang terletak di luar akhir file.

18. Error 417 (Expectation Failed)
Server tidak dapat memenuhi persyaratan bidang "Expect request-header".

19. Error 418 (I'm a Teapot RFC 2324)
Kode ini didefinisikan pada tahun 1998 sebagai salah satu "IETF April Fools Jokes", dalam "RFC 2324", "Hyper Text Coffee Pot Control Protocol", dan tidak untuk dilaksanakan oleh server HTTP yang sebenarnya. Namun, implementasi yang diketahui memang ada.

20. Error 419 (Unused)
Status error ini adalah sebagai kode yang sudah tidak terpakai lagi.

21. Error 420 (Enhance Your Calm)
Status error ini digunakan kembali oleh Twitter, tepatnya pada Twitter Search and Trends API, untuk memberitahukan bahwa akses kita terbatas atau dibatasi. Umumnya layanan lain akan menampilkan status "429 Too Many Request".

22. Error 422 (Unprocessable Entity WebDAV RFC 4918)
Permintaan tersebut adalah "well-formed" tetapi tidak dapat diikuti karena kesalahan semantik.

23. Error 423 (Locked WebDAV RFC 4918)
Kode error ini adalah sumber daya yang sedang diakses terkunci.

24. Error 424 (Failed Dependency WebDAV RFC 4918)
Kode error ini mwnandakan bahwa permintaan gagal karena kegagalan permintaan sebelumnya.

25. Error 425 (Unordered Collection RFC 3648)
Didefinisikan dalam draft "WebDAV Advanced Collections Protocol", tetapi tidak hadir dalam "Web Distributed Authoring and Versioning (WebDAV) Ordered Collections Protocol".

26. Error 426 (Upgrade Required RFC 2817)
Klien diminta untuk menggunakan protocol yang lebih baru.

27. Error 428 Precondition Required
Server asal memerlukan permintaan untuk menjadi bersyarat. Dimaksudkan untuk mencegah hilang update yang masalah, di mana klien "GETs" pada sumber daya itu, telah dimodifikasi, dan menempatkan kembali ke server, ketika sementara pihak ketiga telah dimodifikasi di server, yang mengarah ke konflik. Ditentukan dalam Draft internet yang disetujui untuk dipublikasikan sebagai RFC.

28. Error 429 (Too Many Requests RFC 6585)
Menandakan bahwa user telah mengirimkan terlalu banyak request dalam jangka waktu tertentu. Umumnya server akan memblokir IP yang mengirimkan terlalu banyak request pada waktu tertentu.

29. Error 431 (Request Header Fields Too Large)
Server tidak bersedia untuk memproses permintaan tersebut karena baik sebagai kolom header individu, atau semua bidang header kolektif, terlalu besar. Ditentukan dalam Draft internet yang disetujui untuk dipublikasikan sebagai RFC.

30. Error 444 (No Response Nginx)
Sebuah ekstensi pada Nginx HTTP server. Server mengembalikan tidak ada informasi kepada klien dan menutup koneksi (berguna sebagai pencegah malware).

31. Error 449 (Retry With Microsoft)
Sebuah ekstensi pada Microsoft. Permintaan harus dicoba setelah melakukan tindakan yang sesuai.

32. Error 450 (Blocked by Windows Parental Controls Microsoft)
Artinya akses kepada website tersebut telah diblokir oleh sistem keamanan yang ada pada Windows Parental Control Windows, parental Control berguna untuk memblokir situs-situs yang berbau pornografi.

33. Error 451 (Unavailable For Legal Reasons Internet Draft)
Status ini memberitahukan bahwa akses ditolak karena alasan resmi atau legal. Umumnya jika suatu akses pada sebuah situs website atau pada blog telah diblok oleh pihak sensor atau pemerintah, yang memiliki wewenang untuk melakukan hal tersebut. Contohnya adalah pada situs Megaupload yang telah ditutup oleh FBI karena mengandung konten yang ilegal.

34. Error 499 (Client Closed Request Nginx)
Sebuah ekstensi pada Nginx HTTP server. Kode ini diperkenalkan untuk login kasus ketika koneksi ditutup oleh klien sementara HTTP server memproses permintaan tersebut, membuat server tidak dapat mengirim header HTTP kembali.

35. Error 500 (Internal Server Error)
Kode error ini merupakan kesalahan konfigurasi pada akun hosting. Silahkan Anda cek file .htaccess pada akun hosting Anda dan pastikan setiap barisnya tertulis dengan benar sesuai dengan standar kodenya.

36. Error 501 (Not Implemented)
Server tidak mengenali metode permintaan, atau tidak memiliki kemampuan untuk memenuhi permintaan tersebut.

37. Error 502 (Bad Gateway)
Server bertindak sebagai gateway atau proxy dan menerima respon tidak valid dari server hulu.

38. Error 503 (Service Unavailable)
Server saat ini tidak tersedia karena kelebihan beban atau sedang maintenance. Umumnya, ini adalah keadaan sementara.

39. Error 504 (Gateway Timeout)
Server bertindak sebagai gateway atau proxy dan tidak menerima respon yang tepat waktu dari server hulu.

40. Error 505 (HTTP Version Not Supported)
Server tidak mendukung versi protokol HTTP digunakan dalam permintaan.

41. Error 506 (Variant Also Negotiates RFC 2295)
Isi dari "content negotiation" yang merupakan hasil permintaan dari "a circular reference".

42. Error 507 (Insufficient Storage WebDAV RFC 4918)
Server tidak dapat menyimpan representasi yang dibutuhkan untuk menyelesaikan permintaan tersebut.

43. Error 508 (Loop Detected WebDAV  RFC 5842)
Server terdeteksi pengulangan secara terus menerus saat memproses permintaan (dikirim sebagai pengganti 208).

44. Error 509 (Bandwidth Limited Axceeded)
Kode error ini menandakan bahwa penggunaan bandwidth pada akun hosting sobat sudah melebihi batas maksimal atau dengan kata lainover quota.

45. Error 510 (Not Extended RFC 2774)
Ekstensi permintaan lebih lanjut yang diperlukan server untuk memenuhinya.

46. Error 511 (Network Authentication Required Approved Internet-Draft)
Klien perlu mengotentikasi untuk mendapatkan akses jaringan. Dimaksudkan untuk digunakan untuk mencegat proxy yang digunakan untuk mengontrol akses ke jaringan (misalnya "captive portal" yang digunakan untuk meminta perjanjian untuk Persyaratan Layanan sebelum memberikan akses internet secara penuh melalui hotspot Wi-Fi). Ditentukan dalam Draft internet yang disetujui untuk dipublikasikan sebagai RFC.

47. Error 598 (Network Read Timeout Error)
Ini kode status tidak ditentukan dalam RFC, tapi digunakan oleh beberapa proxy HTTP untuk sinyal jaringan membaca batas waktu di belakang proxy ke client di depan proxy.

*/