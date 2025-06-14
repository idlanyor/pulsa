<?php

class ProviderController {
    private $db;
    
    public function __construct() {
        global $db;
        $this->db = $db;
    }
    
    public function index($page = 1, $limit = 20) {
        $offset = ($page - 1) * $limit;
        
        $query = "SELECT * FROM providers ORDER BY name ASC LIMIT ? OFFSET ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("ii", $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $providers = [];
        while ($row = $result->fetch_assoc()) {
            $providers[] = $row;
        }
        
        // Get total count
        $countQuery = "SELECT COUNT(*) as total FROM providers";
        $countResult = $this->db->query($countQuery);
        $total = $countResult->fetch_assoc()['total'];
        
        return [
            'data' => $providers,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'total_pages' => ceil($total / $limit)
        ];
    }
    
    public function show($id) {
        $query = "SELECT * FROM providers WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_assoc();
    }
    
    public function create($data) {
        if (empty($data['name'])) {
            return ['error' => 'Provider name is required'];
        }
        
        $query = "INSERT INTO providers (name, contact, address, notes) VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("ssss", 
            $data['name'],
            $data['contact'] ?? '',
            $data['address'] ?? '',
            $data['notes'] ?? ''
        );
        
        if ($stmt->execute()) {
            return $this->show($stmt->insert_id);
        }
        
        return ['error' => 'Failed to create provider'];
    }
    
    public function update($id, $data) {
        if (empty($data['name'])) {
            return ['error' => 'Provider name is required'];
        }
        
        $query = "UPDATE providers SET name = ?, contact = ?, address = ?, notes = ? WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("ssssi", 
            $data['name'],
            $data['contact'] ?? '',
            $data['address'] ?? '',
            $data['notes'] ?? '',
            $id
        );
        
        if ($stmt->execute()) {
            return $this->show($id);
        }
        
        return ['error' => 'Failed to update provider'];
    }
    
    public function delete($id) {
        // Check if provider has any associated products
        $checkQuery = "SELECT COUNT(*) as count FROM products WHERE provider_id = ?";
        $checkStmt = $this->db->prepare($checkQuery);
        $checkStmt->bind_param("i", $id);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        $count = $result->fetch_assoc()['count'];
        
        if ($count > 0) {
            return ['error' => 'Cannot delete provider with associated products'];
        }
        
        $query = "DELETE FROM providers WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            return ['message' => 'Provider deleted successfully'];
        }
        
        return ['error' => 'Failed to delete provider'];
    }
} 