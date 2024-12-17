<?php
// Подключение к базе данных
require_once 'dbconnection.php';

// Определяем действие из запроса
$action = $_POST['action'] ?? null;
$response = [];

switch ($action) {
    case 'create_lpf':
        createLpf($pdo);
        break;
    case 'edit_lpf':
        editLpf($pdo);
        break;
    case 'delete_lpf':
        deleteLpf($pdo);
        break;
    case 'link_equipment':
        linkEquipment($pdo);
        break;
    default:
        $response['error'] = 'Неизвестное действие';
}

header('Content-Type: application/json');
echo json_encode($response);

// Создание ЛПФ
function createLpf($pdo) {
    global $response;

    $name = $_POST['name'] ?? '';
    $address = $_POST['address'] ?? '';
    $schedule = $_POST['schedule'] ?? '';

    if (!$name || !$address) {
        $response['error'] = 'Название и адрес обязательны';
        return;
    }

    try {
        $stmt = $pdo->prepare('INSERT INTO lpf (name, address, schedule) VALUES (:name, :address, :schedule) RETURNING id');
        $stmt->execute(['name' => $name, 'address' => $address, 'schedule' => $schedule]);
        $response['success'] = true;
        $response['id'] = $stmt->fetchColumn();
    } catch (PDOException $e) {
        $response['error'] = 'Ошибка при создании ЛПФ: ' . $e->getMessage();
    }
}

// Редактирование ЛПФ
function editLpf($pdo) {
    global $response;

    $id = $_POST['id'] ?? null;
    $name = $_POST['name'] ?? '';
    $address = $_POST['address'] ?? '';
    $schedule = $_POST['schedule'] ?? '';

    if (!$id || !$name || !$address) {
        $response['error'] = 'ID, название и адрес обязательны';
        return;
    }

    try {
        $stmt = $pdo->prepare('UPDATE lpf SET name = :name, address = :address, schedule = :schedule WHERE id = :id');
        $stmt->execute(['id' => $id, 'name' => $name, 'address' => $address, 'schedule' => $schedule]);
        $response['success'] = true;
    } catch (PDOException $e) {
        $response['error'] = 'Ошибка при редактировании ЛПФ: ' . $e->getMessage();
    }
}

// Удаление ЛПФ
function deleteLpf($pdo) {
    global $response;

    $id = $_POST['id'] ?? null;

    if (!$id) {
        $response['error'] = 'ID ЛПФ обязателен';
        return;
    }

    try {
        $stmt = $pdo->prepare('DELETE FROM lpf WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $response['success'] = true;
    } catch (PDOException $e) {
        $response['error'] = 'Ошибка при удалении ЛПФ: ' . $e->getMessage();
    }
}

// Привязка техники
function linkEquipment($pdo) {
    global $response;

    $equipmentData = $_POST['equipment_data'] ?? [];
    $lpfId = $_POST['lpf_id'] ?? null;

    if (!$lpfId || empty($equipmentData)) {
        $response['error'] = 'ID ЛПФ и данные техники обязательны';
        return;
    }

    try {
        foreach ($equipmentData as $equipment) {
            $stmt = $pdo->prepare('SELECT id FROM equipment WHERE reg_number = :reg_number AND operator_name = :operator_name');
            $stmt->execute(['reg_number' => $equipment['reg_number'], 'operator_name' => $equipment['operator_name']]);
            $equipmentId = $stmt->fetchColumn();

            if (!$equipmentId) {
                // Создать новую технику
                $stmt = $pdo->prepare('INSERT INTO equipment (reg_number, operator_name) VALUES (:reg_number, :operator_name) RETURNING id');
                $stmt->execute(['reg_number' => $equipment['reg_number'], 'operator_name' => $equipment['operator_name']]);
                $equipmentId = $stmt->fetchColumn();
            }

            // Привязать технику к ЛПФ
            $stmt = $pdo->prepare('INSERT INTO lpf_equipment (equipment_id, lpf_id) VALUES (:equipment_id, :lpf_id) ON CONFLICT DO NOTHING');
            $stmt->execute(['equipment_id' => $equipmentId, 'lpf_id' => $lpfId]);
        }

        $response['success'] = true;
    } catch (PDOException $e) {
        $response['error'] = 'Ошибка при привязке техники: ' . $e->getMessage();
    }
}
?>
