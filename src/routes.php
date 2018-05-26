<?php

use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\UploadedFile;

// Routes

$app->get('/[{name}]', function (Request $request, Response $response, array $args) {
    // Sample log message
    $this->logger->info("Slim-Skeleton '/' route");

    // Render index view
    return $this->renderer->render($response, 'index.phtml', $args);
});

//rute GET /allmenu/
$app->get("/allmenu/", function (Request $request, Response $response){
    $sql = "SELECT * FROM makanan";
    $stmt = $this->db->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetchAll();
    return $response->withJson(["status" => "success", "data" => $result], 200);
});

//rute GET /menu/1
$app->get("/menu/{id}", function (Request $request, Response $response, $args){
    $id = $args["id"];
    $sql = "SELECT * FROM makanan WHERE id=:id";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([":id" => $id]);
    $result = $stmt->fetch();
    return $response->withJson(["status" => "success", "data" => $result], 200);
});

//rute Get /pesanan/search 
$app->get("/menu/search/", function (Request $request, Response $response, $args){
    $keyword = $request->getQueryParam("keyword");
    $sql = "SELECT * FROM makanan WHERE nama LIKE '%$keyword%' OR harga LIKE '%$keyword%' OR kategori LIKE '%$keyword%'";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([":id" => $id]);
    $result = $stmt->fetchAll();
    return $response->withJson(["status" => "success", "data" => $result], 200);
});

//Rute POST /login
$app->post("/login/", function (Request $request, Response $response){

    $login = $request->getParsedBody();

    $sql = "SELECT * FROM pelanggan WHERE email=:email AND password=:password";
    $stmt = $this->db->prepare($sql);

    $data = [
        ":email" => $login["email"],
        ":password" => $login["password"],
    ];

    $stmt->execute($data);
    $result = $stmt->fetch();


    if($result)
       return $response->withJson(["status" => "success", "data" => $result], 200);
    
    return $response->withJson(["status" => "failed", "data" => "0"], 200);
});


// //Rute POST /pesan/1
$app->post("/pesan/{makanan_id}", function (Request $request, Response $response, $args){
    $id = $args["makanan_id"];
    $new_order = $request->getParsedBody();
    //$sql ="SELECT * FROM makanan WHERE id=:id";
    $sql = "INSERT INTO pesan (pelanggan_id, latit, longit,) VALUE (:pelanggan_id, :latit, :longit)";
    $stmt = $this->db->prepare($sql);
    
    $data = [
        //":id" => $id,
        ":pelanggan_id" => $new_order["pelanggan_id"],
        ":makanan_id" => $id,
        ":latit" => $new_order["latit"],
        ":longit" => $new_order["longit"]
    ];

    if($stmt->execute($data))
       return $response->withJson(["status" => "success", "data" => "1"], 200);
    
    return $response->withJson(["status" => "failed", "data" => "0"], 200);
});

//Rute DELETE /orders/1
// $app->delete("/orders/{id}", function (Request $request, Response $response, $args){
//     $id = $args["id"];
//     $sql = "DELETE FROM orders WHERE order_id=:id";
//     $stmt = $this->db->prepare($sql);
    
//     $data = [
//         ":id" => $id
//     ];

//     if($stmt->execute($data))
//        return $response->withJson(["status" => "success", "data" => "1"], 200);
    
//     return $response->withJson(["status" => "failed", "data" => "0"], 200);
// });


// // membuat middleware
// $cekAPIKey = function($request, $response, $next){
//     // ini middleware untuk cek apikey
// };

// // menambahkan middleware ke route
// $app->get('/orders', function ($request, $response) {
//       // ...
// })->add(cekAPIKey());

$app->post("/pesan/", function (Request $request, Response $response){

    $new_pesan = $request->getParsedBody();

    $sql = "INSERT INTO `pesan`(`nama`, `no_hp`, `latit`, `longit`, `nama_menu`, `jumlah`, `total_harga`, `status`) VALUES (:nama,:no_hp,:latit,:longit,:nama_menu,:jumlah, :total_harga, :status)";
    $stmt = $this->db->prepare($sql);

    $data = [
        ":nama" => $new_pesan["nama"],
        ":no_hp" => $new_pesan["no_hp"],
        ":latit" => $new_pesan["latit"],
        ":longit" => $new_pesan["longit"],
        ":nama_menu" => $new_pesan["nama_menu"],
        ":jumlah" => $new_pesan["jumlah"],
        ":total_harga" => $new_pesan["total_harga"],
        ":status" => $new_pesan["status"]
    ];


    if($stmt->execute($data))
       return $response->withJson(["status" => "success", "data" => $data], 200);

    return $response->withJson(["status" => "failed", "data" => "0"], 200);
});

$app->get("/pesan/lihat", function (Request $request, Response $response){
    $sql = "SELECT * FROM pesan";
    $stmt = $this->db->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetchAll();
    return $response->withJson(["status" => "success", "data" => $result], 200);
});

$app->post('/makanan/gambar/{id}', function(Request $request, Response $response, $args) {
    
    $uploadedFiles = $request->getUploadedFiles();
    
    // handle single input with single file upload
    $uploadedFile = $uploadedFiles['gambar'];
    if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
        
        $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
        
        // ubah nama file dengan id makanan
        $filename = sprintf('%s.%0.8s', $args["id"], $extension);
        
        $directory = $this->get('settings')['upload_directory'];
        $uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);

        // simpan nama file ke database
        $sql = "UPDATE makanan SET gambar=:gambar WHERE id=:id";
        $stmt = $this->db->prepare($sql);
        $params = [
            ":id" => $args["id"],
            ":gambar" => $filename
        ];
        
        if($stmt->execute($params)){
            // ambil base url dan gabungkan dengan file name untuk membentuk URL file
            $url = $request->getUri()->getBaseUrl()."/uploads/".$filename;
            return $response->withJson(["status" => "success", "data" => $url], 200);
        }
        
        return $response->withJson(["status" => "failed", "data" => "0"], 200);
    }
});

// $app->post('/tamabah/makanan', function(Request $request, Response $response, $args) {
    
//     $uploadedFiles = $request->getUploadedFiles();
    
//      // handle multiple inputs with the same key
//      foreach ($uploadedFiles['makanan'] as $uploadedFile) {
//         if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
//             $filename = moveUploadedFile($directory, $uploadedFile);
//             $response->write('uploaded ' . $filename . '<br/>'); {
        
//         $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
        
//         // ubah nama file dengan id makanan
//         //$filename = sprintf('%s.%0.8s', $args["id"], $extension);
        
//         $directory = $this->get('settings')['upload_directory'];
//         $uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);

//         // simpan nama file ke database
//         $sql = "UPDATE makanan SET gambar=:gambar WHERE id=:id";
//         $stmt = $this->db->prepare($sql);
//         $params = [
//             ":id" => $args["id"],
//             ":gambar" => $filename
//         ];
        
//         if($stmt->execute($params)){
//             // ambil base url dan gabungkan dengan file name untuk membentuk URL file
//             $url = $request->getUri()->getBaseUrl()."/uploads/".$filename;
//             return $response->withJson(["status" => "success", "data" => $url], 200);
//         }
        
//         return $response->withJson(["status" => "failed", "data" => "0"], 200);
//     }
// });

//update status pesan
$app->put("/status/pesan/{id}", function (Request $request, Response $response, $args){
    $id = $args["id"];
    $ganti_status = $request->getParsedBody();
    $sql = "UPDATE pesan SET status=:status WHERE id=:id";
    $stmt = $this->db->prepare($sql);
    
    $data = [
        ":id" => $id,
        ":status" => $ganti_status["status"],
    ];

    if($stmt->execute($data))
       return $response->withJson(["status" => "success", "data" => $data], 200);
    
    return $response->withJson(["status" => "failed", "data" => "0"], 200);
});