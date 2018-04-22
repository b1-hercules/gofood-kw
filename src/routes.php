<?php

use Slim\Http\Request;
use Slim\Http\Response;

// Routes

$app->get('/[{name}]', function (Request $request, Response $response, array $args) {
    // Sample log message
    $this->logger->info("Slim-Skeleton '/' route");

    // Render index view
    return $this->renderer->render($response, 'index.phtml', $args);
});

//rute GET /orders/
$app->get("/orders/", function (Request $request, Response $response){
    $sql = "SELECT * FROM orders";
    $stmt = $this->db->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetchAll();
    return $response->withJson(["status" => "success", "data" => $result], 200);
});

//rute GET /orders/1
$app->get("/orders/{id}", function (Request $request, Response $response, $args){
    $id = $args["id"];
    $sql = "SELECT * FROM orders WHERE order_id=:id";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([":id" => $id]);
    $result = $stmt->fetch();
    return $response->withJson(["status" => "success", "data" => $result], 200);
});

//rute Get /orders/search 
$app->get("/orders/search/", function (Request $request, Response $response, $args){
    $keyword = $request->getQueryParam("keyword");
    $sql = "SELECT * FROM orders WHERE nama_order LIKE '%$keyword%' OR keterangan LIKE '%$keyword%' OR pemesan LIKE '%$keyword%'";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([":id" => $id]);
    $result = $stmt->fetchAll();
    return $response->withJson(["status" => "success", "data" => $result], 200);
});

//Rute POST /orders
$app->post("/orders/", function (Request $request, Response $response){

    $new_order = $request->getParsedBody();

    $sql = "INSERT INTO orders (nama_order, pemesan, keterangan) VALUE (:nama_order, :pemesan, :keterangan)";
    $stmt = $this->db->prepare($sql);

    $data = [
        ":nama_order" => $new_order["nama_order"],
        ":pemesan" => $new_order["pemesan"],
        ":keterangan" => $new_order["keterangan"]
    ];

    if($stmt->execute($data))
       return $response->withJson(["status" => "success", "data" => "1"], 200);
    
    return $response->withJson(["status" => "failed", "data" => "0"], 200);
});

//Rute PUT /orders/1
$app->put("/orders/{id}", function (Request $request, Response $response, $args){
    $id = $args["id"];
    $new_order = $request->getParsedBody();
    $sql = "UPDATE orders SET nama_order=:nama_order, pemesan=:pemesan, keterangan=:keterangan WHERE order_id=:id";
    $stmt = $this->db->prepare($sql);
    
    $data = [
        ":id" => $id,
        ":nama_order" => $new_order["nama_order"],
        ":pemesan" => $new_order["pemesan"],
        ":keterangan" => $new_order["keterangan"]
    ];

    if($stmt->execute($data))
       return $response->withJson(["status" => "success", "data" => "1"], 200);
    
    return $response->withJson(["status" => "failed", "data" => "0"], 200);
});

//Rute DELETE /orders/1
$app->delete("/orders/{id}", function (Request $request, Response $response, $args){
    $id = $args["id"];
    $sql = "DELETE FROM orders WHERE order_id=:id";
    $stmt = $this->db->prepare($sql);
    
    $data = [
        ":id" => $id
    ];

    if($stmt->execute($data))
       return $response->withJson(["status" => "success", "data" => "1"], 200);
    
    return $response->withJson(["status" => "failed", "data" => "0"], 200);
});


// // membuat middleware
// $cekAPIKey = function($request, $response, $next){
//     // ini middleware untuk cek apikey
// };

// // menambahkan middleware ke route
// $app->get('/orders', function ($request, $response) {
//       // ...
// })->add(cekAPIKey());


