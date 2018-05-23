<?php
// Application middleware

// e.g: $app->add(new \Slim\Csrf\Guard);

// middleware untuk validasi api key
$app->add(function ($request, $response, $next) {
    
    $key = $request->getQueryParam("key");

    if(!isset($key)){
        return $response->withJson(["status" => "API Key required"], 401);
    }
    
    $sql = "SELECT * FROM pelanggan WHERE api_key=:api_key";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([":api_key" => $key]);
    
    if($stmt->rowCount() > 0){
        $result = $stmt->fetch();
        if($key == $result["api_key"]){
        
            // update hit
            $sql = "UPDATE pelanggan SET hit=hit+1 WHERE api_key=:api_key";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([":api_key" => $key]);
            
            return $response = $next($request, $response);
        }
    }

    return $response->withJson(["status" => "Unauthorized"], 401);

});


// $app->group('/api', function () use ($app) {
//     $app->get('/orders', function ($request, $response) {
//       // ...
//     });
//    $app->get('/orders/{id}', function ($request, $response) {
//        // ...
//     });
// })->add(cekAPIKey());
