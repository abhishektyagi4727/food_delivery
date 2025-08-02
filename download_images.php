<?php
// Sample image URLs
$images = [
    'margherita.jpg' => 'https://images.unsplash.com/photo-1604382354936-07c5d9983bd3',
    'pepperoni.jpg' => 'https://images.unsplash.com/photo-1628840042765-356cda07504e',
    'veggie_pizza.jpg' => 'https://images.unsplash.com/photo-1511689660979-10d2b1aada49',
    'classic_burger.jpg' => 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd',
    'chicken_burger.jpg' => 'https://images.unsplash.com/photo-1610440042657-612c34d95e9f',
    'veggie_burger.jpg' => 'https://images.unsplash.com/photo-1585238342024-78d387f4a707',
    'california_roll.jpg' => 'https://images.unsplash.com/photo-1617196034796-73dfa7b1fd56',
    'salmon_nigiri.jpg' => 'https://images.unsplash.com/photo-1579584425555-c3ce17fd4351',
    'vegetable_roll.jpg' => 'https://images.unsplash.com/photo-1617196034796-73dfa7b1fd56',
    'chocolate_cake.jpg' => 'https://images.unsplash.com/photo-1578985545062-69928b1d9587',
    'ice_cream.jpg' => 'https://images.unsplash.com/photo-1567206563064-6f60f40a2b57',
    'cheesecake.jpg' => 'https://images.unsplash.com/photo-1524351199678-941a58a3df50',
    'cola.jpg' => 'https://images.unsplash.com/photo-1622483767028-3f66f32aef97',
    'juice.jpg' => 'https://images.unsplash.com/photo-1600271886742-f049cd451bba',
    'water.jpg' => 'https://www.google.com/search?sca_esv=5ea039d3f5929d6f&rlz=1C1CHZN_enIN1129IN1129&sxsrf=AHTn8zpQwKKVrBYQecbD0bujw5dqWVwn7A:1747480197792&q=grains+and+pulses+images&udm=2&fbs=ABzOT_CWdhQLP1FcmU5B0fn3xuWpA-dk4wpBWOGsoR7DG5zJBsxayPSIAqObp_AgjkUGqengxVrJ7hrmYmz7X2OZp_NIYfhIAjPnSJLO3GH6L0gKvtZEMTLpBvONHPnetXvTklGBGnVV0SzYjSPlLGg8ky-KbuJwS5m66CDOFhTlME8F-nscMnTMU-8dJQgAGZ8zkbHLJZ1sFxwmU0milh3hV1ou_Ag-nA&sa=X&ved=2ahUKEwiX4cODr6qNAxUtSGcHHS9FOR8QtKgLegQIDhAB&biw=1360&bih=641&dpr=1#vhid=8-KVIBoygRT85M&vssid=mosaic'
];

// Create uploads directory if it doesn't exist
$upload_dir = __DIR__ . '/uploads/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Download images
foreach ($images as $filename => $url) {
    $filepath = $upload_dir . $filename;
    if (!file_exists($filepath)) {
        $image_content = file_get_contents($url);
        if ($image_content !== false) {
            file_put_contents($filepath, $image_content);
            echo "Downloaded: $filename\n";
        } else {
            echo "Failed to download: $filename\n";
        }
    } else {
        echo "File already exists: $filename\n";
    }
}

echo "All images have been downloaded!";
?>
