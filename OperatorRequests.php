<?php
// Подключение к базе данных
require_once 'dbconnection.php';

// Определяем действие из запроса
$action = $_POST['action'] ?? null;
$response = [];

switch ($action) {
    case 'add_equipment':
        addEquipment($pdo);
        break;
    case 'view_lpf':
        viewLpf($pdo);
        break;
    default:
        $response['error'] = 'Неизвестное действие';
}

header('Content-Type: application/json');
echo json_encode($response);

// Добавление техники
function addEquipment($pdo) {
    global $response;

    $regNumber = $_POST['reg_number'] ?? '';
    $operatorName = $_POST['operator_name'] ?? '';

    if (!$regNumber || !$operatorName) {
        $response['error'] = 'Регистрационный номер и имя оператора обязательны';
        return;
    }

    try {
        $stmt = $pdo->prepare('INSERT INTO equipment (reg_number, operator_name) VALUES (:reg_number, :operator_name) RETURNING id');
        $stmt->execute(['reg_number' => $regNumber, 'operator_name' => $operatorName]);
        $response['success'] = true;
        $response['id'] = $stmt->fetchColumn();
    } catch (PDOException $e) {
        $response['error'] = 'Ошибка при добавлении техники: ' . $e->getMessage();
    }
}

// Просмотр связанных ЛПФ
function viewLpf($pdo) {
    global $response;

    $operatorName = $_POST['operator_name'] ?? '';

    if (!$operatorName) {
        $response['error'] = 'Имя оператора обязательно';
        return;
    }

    try {
        $stmt = $pdo->prepare('SELECT lpf.name, lpf.address FROM lpf 
                               INNER JOIN lpf_equipment le ON lpf.id = le.lpf_id
                               INNER JOIN equipment e ON e.id = le.equipment_id
                               WHERE e.operator_name = :operator_name');
        $stmt->execute(['operator_name' => $operatorName]);
        $response['success'] = true;
        $response['data'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $response['error'] = 'Ошибка при получении связанных ЛПФ: ' . $e->getMessage();
    }
}
?>
