<?php
// Include database connection
require_once 'db_connect.php';

/**
 * Create a new record in the specified table
 * 
 * @param string $table The table name
 * @param array $data Associative array of column names and values
 * @return int|bool The ID of the inserted record or false on failure
 */
function create_record($table, $data) {
    global $conn;
    
    try {
        // Build the SQL query
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        
        $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
        
        // Prepare and execute the statement
        $stmt = $conn->prepare($sql);
        
        if ($stmt === false) {
            throw new Exception("Error preparing statement: " . $conn->error);
        }
        
        // Bind parameters
        $types = str_repeat('s', count($data)); // Assuming all strings for simplicity
        $values = array_values($data);
        
        $stmt->bind_param($types, ...$values);
        
        // Execute the statement
        if ($stmt->execute()) {
            $id = $stmt->insert_id;
            $stmt->close();
            return $id;
        } else {
            throw new Exception("Error executing statement: " . $stmt->error);
        }
    } catch (Exception $e) {
        error_log("Create record error: " . $e->getMessage());
        return false;
    }
}

/**
 * Read records from the specified table
 * 
 * @param string $table The table name
 * @param array $columns Columns to select (default: all)
 * @param array $where Associative array of where conditions
 * @param string $order_by Order by clause
 * @param int $limit Limit the number of records
 * @param int $offset Offset for pagination
 * @return array|bool Array of records or false on failure
 */
function read_records($table, $columns = ['*'], $where = [], $order_by = '', $limit = 0, $offset = 0) {
    global $conn;
    
    try {
        // Build the SQL query
        $columns_str = implode(', ', $columns);
        $sql = "SELECT $columns_str FROM $table";
        
        // Add WHERE clause if conditions are provided
        if (!empty($where)) {
            $conditions = [];
            foreach ($where as $column => $value) {
                $conditions[] = "$column = ?";
            }
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }
        
        // Add ORDER BY clause if provided
        if (!empty($order_by)) {
            $sql .= " ORDER BY $order_by";
        }
        
        // Add LIMIT and OFFSET if provided
        if ($limit > 0) {
            $sql .= " LIMIT $limit";
            if ($offset > 0) {
                $sql .= " OFFSET $offset";
            }
        }
        
        // Prepare the statement
        $stmt = $conn->prepare($sql);
        
        if ($stmt === false) {
            throw new Exception("Error preparing statement: " . $conn->error);
        }
        
        // Bind parameters for WHERE clause
        if (!empty($where)) {
            $types = str_repeat('s', count($where)); // Assuming all strings for simplicity
            $values = array_values($where);
            
            $stmt->bind_param($types, ...$values);
        }
        
        // Execute the statement
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            $records = [];
            
            while ($row = $result->fetch_assoc()) {
                $records[] = $row;
            }
            
            $stmt->close();
            return $records;
        } else {
            throw new Exception("Error executing statement: " . $stmt->error);
        }
    } catch (Exception $e) {
        error_log("Read records error: " . $e->getMessage());
        return false;
    }
}

/**
 * Get a single record by ID
 * 
 * @param string $table The table name
 * @param int $id The record ID
 * @param array $columns Columns to select (default: all)
 * @return array|bool The record or false on failure
 */
function get_record_by_id($table, $id, $columns = ['*']) {
    global $conn;
    
    try {
        // Build the SQL query
        $columns_str = implode(', ', $columns);
        $sql = "SELECT $columns_str FROM $table WHERE id = ?";
        
        // Prepare the statement
        $stmt = $conn->prepare($sql);
        
        if ($stmt === false) {
            throw new Exception("Error preparing statement: " . $conn->error);
        }
        
        // Bind parameter
        $stmt->bind_param('i', $id);
        
        // Execute the statement
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            $record = $result->fetch_assoc();
            
            $stmt->close();
            return $record;
        } else {
            throw new Exception("Error executing statement: " . $stmt->error);
        }
    } catch (Exception $e) {
        error_log("Get record by ID error: " . $e->getMessage());
        return false;
    }
}

/**
 * Update a record in the specified table
 * 
 * @param string $table The table name
 * @param int $id The record ID
 * @param array $data Associative array of column names and values to update
 * @return bool True on success, false on failure
 */
function update_record($table, $id, $data) {
    global $conn;
    
    try {
        // Build the SQL query
        $set_clause = [];
        foreach ($data as $column => $value) {
            $set_clause[] = "$column = ?";
        }
        $set_str = implode(', ', $set_clause);
        
        $sql = "UPDATE $table SET $set_str WHERE id = ?";
        
        // Log the SQL query for debugging
        error_log("SQL Query: " . $sql);
        error_log("Data to update: " . print_r($data, true));
        error_log("ID to update: " . $id);
        
        // Prepare the statement
        $stmt = $conn->prepare($sql);
        
        if ($stmt === false) {
            throw new Exception("Error preparing statement: " . $conn->error);
        }
        
        // Bind parameters
        $types = str_repeat('s', count($data)) . 'i'; // Assuming all strings for data + integer for ID
        $values = array_values($data);
        $values[] = $id;
        
        // Log the bind parameters for debugging
        error_log("Types string: " . $types);
        error_log("Values to bind: " . print_r($values, true));
        
        $stmt->bind_param($types, ...$values);
        
        // Execute the statement
        $execute_result = $stmt->execute();
        error_log("Execute result: " . ($execute_result ? 'Success' : 'Failed'));
        
        if ($execute_result) {
            $affected_rows = $stmt->affected_rows;
            error_log("Affected rows: " . $affected_rows);
            $stmt->close();
            return $affected_rows > 0;
        } else {
            throw new Exception("Error executing statement: " . $stmt->error);
        }
    } catch (Exception $e) {
        error_log("Update record error: " . $e->getMessage());
        return false;
    }
}

/**
 * Delete a record from the specified table
 * 
 * @param string $table The table name
 * @param int $id The record ID
 * @return bool True on success, false on failure
 */
function delete_record($table, $id) {
    global $conn;
    
    try {
        // Build the SQL query
        $sql = "DELETE FROM $table WHERE id = ?";
        
        // Prepare the statement
        $stmt = $conn->prepare($sql);
        
        if ($stmt === false) {
            throw new Exception("Error preparing statement: " . $conn->error);
        }
        
        // Bind parameter
        $stmt->bind_param('i', $id);
        
        // Execute the statement
        if ($stmt->execute()) {
            $affected_rows = $stmt->affected_rows;
            $stmt->close();
            return $affected_rows > 0;
        } else {
            throw new Exception("Error executing statement: " . $stmt->error);
        }
    } catch (Exception $e) {
        error_log("Delete record error: " . $e->getMessage());
        return false;
    }
}

/**
 * Count records in the specified table
 * 
 * @param string $table The table name
 * @param array $where Associative array of where conditions
 * @return int|bool The count or false on failure
 */
function count_records($table, $where = []) {
    global $conn;
    
    try {
        // Build the SQL query
        $sql = "SELECT COUNT(*) as count FROM $table";
        
        // Add WHERE clause if conditions are provided
        if (!empty($where)) {
            $conditions = [];
            foreach ($where as $column => $value) {
                $conditions[] = "$column = ?";
            }
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }
        
        // Prepare the statement
        $stmt = $conn->prepare($sql);
        
        if ($stmt === false) {
            throw new Exception("Error preparing statement: " . $conn->error);
        }
        
        // Bind parameters for WHERE clause
        if (!empty($where)) {
            $types = str_repeat('s', count($where)); // Assuming all strings for simplicity
            $values = array_values($where);
            
            $stmt->bind_param($types, ...$values);
        }
        
        // Execute the statement
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            $stmt->close();
            return (int) $row['count'];
        } else {
            throw new Exception("Error executing statement: " . $stmt->error);
        }
    } catch (Exception $e) {
        error_log("Count records error: " . $e->getMessage());
        return false;
    }
}

/**
 * Search records in the specified table
 * 
 * @param string $table The table name
 * @param array $search_columns Columns to search in
 * @param string $search_term The search term
 * @param array $columns Columns to select (default: all)
 * @param string $order_by Order by clause
 * @param int $limit Limit the number of records
 * @param int $offset Offset for pagination
 * @return array|bool Array of records or false on failure
 */
function search_records($table, $search_columns, $search_term, $columns = ['*'], $order_by = '', $limit = 0, $offset = 0) {
    global $conn;
    
    try {
        // Build the SQL query
        $columns_str = implode(', ', $columns);
        $sql = "SELECT $columns_str FROM $table";
        
        // Add WHERE clause for search
        if (!empty($search_columns) && !empty($search_term)) {
            $search_conditions = [];
            foreach ($search_columns as $column) {
                $search_conditions[] = "$column LIKE ?";
            }
            $sql .= " WHERE " . implode(' OR ', $search_conditions);
        }
        
        // Add ORDER BY clause if provided
        if (!empty($order_by)) {
            $sql .= " ORDER BY $order_by";
        }
        
        // Add LIMIT and OFFSET if provided
        if ($limit > 0) {
            $sql .= " LIMIT $limit";
            if ($offset > 0) {
                $sql .= " OFFSET $offset";
            }
        }
        
        // Prepare the statement
        $stmt = $conn->prepare($sql);
        
        if ($stmt === false) {
            throw new Exception("Error preparing statement: " . $conn->error);
        }
        
        // Bind parameters for search term
        if (!empty($search_columns) && !empty($search_term)) {
            $types = str_repeat('s', count($search_columns)); // Assuming all strings for simplicity
            $search_values = array_fill(0, count($search_columns), "%$search_term%");
            
            $stmt->bind_param($types, ...$search_values);
        }
        
        // Execute the statement
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            $records = [];
            
            while ($row = $result->fetch_assoc()) {
                $records[] = $row;
            }
            
            $stmt->close();
            return $records;
        } else {
            throw new Exception("Error executing statement: " . $stmt->error);
        }
    } catch (Exception $e) {
        error_log("Search records error: " . $e->getMessage());
        return false;
    }
}
?>