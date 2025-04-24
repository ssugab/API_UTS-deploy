<?php
class AddressService {
    private $baseUrl = 'https://alamat.thecloudalert.com/api';
    
    /**
     * Get data from external API
     */
    private function fetchFromApi($endpoint, $params = []) {
        $url = $this->baseUrl . $endpoint;
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new Exception("API request failed with code: " . $httpCode);
        }
        
        return json_decode($response, true);
    }
    
    /**
     * Get all provinces
     */
    public function getProvinces() {
        try {
            $response = $this->fetchFromApi('/provinsi/get/');
            if ($response['status'] === 200) {
                global $db;
                
                // Begin transaction
                $db->beginTransaction();
                
                // Prepare insert statement
                $stmt = $db->prepare("INSERT INTO provinces (name, code) VALUES (?, ?) 
                                    ON DUPLICATE KEY UPDATE name = VALUES(name)");
                
                foreach ($response['result'] as $province) {
                    $stmt->execute([$province['text'], $province['id']]);
                }
                
                $db->commit();
                return true;
            }
            return false;
        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            error_log("Error syncing provinces: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get cities by province ID
     */
    public function getCities($provinceId) {
        try {
            $response = $this->fetchFromApi('/kabkota/get/', ['d_provinsi_id' => $provinceId]);
            if ($response['status'] === 200) {
                global $db;
                
                $db->beginTransaction();
                
                $stmt = $db->prepare("INSERT INTO cities (province_id, name, code, type) 
                                    VALUES (?, ?, ?, ?) 
                                    ON DUPLICATE KEY UPDATE 
                                    name = VALUES(name), 
                                    type = VALUES(type)");
                
                foreach ($response['result'] as $city) {
                    // Determine if it's kabupaten or kota
                    $type = (strpos(strtolower($city['text']), 'kabupaten') !== false) ? 'kabupaten' : 'kota';
                    $name = str_replace(['Kabupaten ', 'Kota '], '', $city['text']);
                    
                    $stmt->execute([$provinceId, $name, $city['id'], $type]);
                }
                
                $db->commit();
                return true;
            }
            return false;
        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            error_log("Error syncing cities: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get districts by city ID
     */
    public function getDistricts($cityId) {
        try {
            $response = $this->fetchFromApi('/kecamatan/get/', ['d_kabkota_id' => $cityId]);
            if ($response['status'] === 200) {
                global $db;
                
                $db->beginTransaction();
                
                $stmt = $db->prepare("INSERT INTO districts (city_id, name, code) 
                                    VALUES (?, ?, ?) 
                                    ON DUPLICATE KEY UPDATE name = VALUES(name)");
                
                foreach ($response['result'] as $district) {
                    $stmt->execute([$cityId, $district['text'], $district['id']]);
                }
                
                $db->commit();
                return true;
            }
            return false;
        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            error_log("Error syncing districts: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get villages by district ID
     */
    public function getVillages($districtId) {
        try {
            $response = $this->fetchFromApi('/kelurahan/get/', ['d_kecamatan_id' => $districtId]);
            if ($response['status'] === 200) {
                global $db;
                
                $db->beginTransaction();
                
                $stmt = $db->prepare("INSERT INTO villages (district_id, name, code) 
                                    VALUES (?, ?, ?) 
                                    ON DUPLICATE KEY UPDATE name = VALUES(name)");
                
                foreach ($response['result'] as $village) {
                    $stmt->execute([$districtId, $village['text'], $village['id']]);
                }
                
                $db->commit();
                return true;
            }
            return false;
        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            error_log("Error syncing villages: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Sync postal codes
     */
    public function syncPostalCodes($cityId, $districtId) {
        try {
            $response = $this->fetchFromApi('/kodepos/get/', [
                'd_kabkota_id' => $cityId,
                'd_kecamatan_id' => $districtId
            ]);
            
            if ($response['status'] === 200) {
                global $db;
                
                $db->beginTransaction();
                
                // Update postal codes for villages in this district
                $stmt = $db->prepare("UPDATE villages SET postal_code = ? 
                                    WHERE district_id = ? AND postal_code IS NULL");
                
                foreach ($response['result'] as $postalCode) {
                    $stmt->execute([$postalCode['text'], $districtId]);
                }
                
                $db->commit();
                return true;
            }
            return false;
        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            error_log("Error syncing postal codes: " . $e->getMessage());
            return false;
        }
    }
} 