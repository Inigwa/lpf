<?php
// Подключение к базе данных
require_once 'dbconnection.php';

header('Content-Type: application/json');

try {
    // Запрос на получение данных о ЛПФ и связанной технике
    $query = "
        SELECT 
            lpf.id AS lpf_id, 
            lpf.name AS lpf_name, 
            lpf.address AS lpf_address, 
            lpf.schedule AS lpf_schedule,
            json_agg(
                json_build_object(
                    'equipment_id', equipment.id, 
                    'reg_number', equipment.reg_number, 
                    'operator_name', equipment.operator_name
                )
            ) AS equipment
        FROM lpf
        LEFT JOIN lpf_equipment ON lpf.id = lpf_equipment.lpf_id
        LEFT JOIN equipment ON lpf_equipment.equipment_id = equipment.id
        GROUP BY lpf.id
        ORDER BY lpf.name;
    ";

    $stmt = $pdo->prepare($query);
    $stmt->execute();

    $lpfData = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["success" => true, "data" => $lpfData]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "error" => "Ошибка при получении данных: " . $e->getMessage()]);
}
