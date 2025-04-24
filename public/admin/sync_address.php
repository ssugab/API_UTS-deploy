<?php
require_once '../config/config.php';
require_once '../includes/AddressService.php';

// Only allow admin access
if (!isLoggedIn() || !isAdmin()) {
    redirect(BASE_URL . '/auth/login.php', 'Access denied', 'error');
}

try {
    $addressService = new AddressService();
    
    // Step 1: Sync provinces
    echo "Syncing provinces...\n";
    if ($addressService->getProvinces()) {
        echo "Provinces synced successfully.\n";
        
        // Step 2: Get all provinces from our database
        $stmt = $db->query("SELECT id, code FROM provinces");
        $provinces = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($provinces as $province) {
            echo "Syncing cities for province {$province['id']}...\n";
            
            // Step 3: Sync cities for each province
            if ($addressService->getCities($province['code'])) {
                echo "Cities synced successfully for province {$province['id']}.\n";
                
                // Step 4: Get all cities from our database for this province
                $stmt = $db->prepare("SELECT id, code FROM cities WHERE province_id = ?");
                $stmt->execute([$province['id']]);
                $cities = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                foreach ($cities as $city) {
                    echo "Syncing districts for city {$city['id']}...\n";
                    
                    // Step 5: Sync districts for each city
                    if ($addressService->getDistricts($city['code'])) {
                        echo "Districts synced successfully for city {$city['id']}.\n";
                        
                        // Step 6: Get all districts from our database for this city
                        $stmt = $db->prepare("SELECT id, code FROM districts WHERE city_id = ?");
                        $stmt->execute([$city['id']]);
                        $districts = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        
                        foreach ($districts as $district) {
                            echo "Syncing villages for district {$district['id']}...\n";
                            
                            // Step 7: Sync villages for each district
                            if ($addressService->getVillages($district['code'])) {
                                echo "Villages synced successfully for district {$district['id']}.\n";
                                
                                // Step 8: Sync postal codes
                                if ($addressService->syncPostalCodes($city['code'], $district['code'])) {
                                    echo "Postal codes synced successfully for district {$district['id']}.\n";
                                }
                            }
                        }
                    }
                }
            }
        }
        
        echo "All data synced successfully!\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    error_log("Sync Error: " . $e->getMessage());
} 