<?php
// Подключение к базе данных
require_once 'dbconnection.php';

header('Content-Type: application/json');

try {
    // Запрос на получение данных о технике и связанных ЛПФ
    $query = "
        SELECT 
            equipment.id AS equipment_id,
            equipment.reg_number AS registration_number,
            equipment.operator_name AS operator_name,
            json_agg(
                DISTINCT json_build_object(
                    'lpf_id', lpf.id,
                    'lpf_name', lpf.name,
                    'lpf_address', lpf.address
                )
            ) AS linked_lpf
        FROM equipment
        LEFT JOIN lpf_equipment ON equipment.id = lpf_equipment.equipment_id
        LEFT JOIN lpf ON lpf_equipment.lpf_id = lpf.id
        GROUP BY equipment.id
        ORDER BY equipment.reg_number;
    ";

    $stmt = $pdo->prepare($query);
    $stmt->execute();

    $equipmentData = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Формируем ответ в формате JSON
    echo json_encode(["success" => true, "data" => $equipmentData]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "error" => "Ошибка при получении данных: " . $e->getMessage()]);
}
