<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once __DIR__ . '/../config/Database.php';

$connection = (new Database())->connect();
$connection->beginTransaction();

$unitByCategory = [
    'Food Items' => 'kg',
    'Hygiene Kits' => 'kit',
    'Clothing' => 'piece',
    'Medical Supplies' => 'pack',
    'Household Items' => 'piece',
    'School Supplies' => 'set'
];
$perishableCategories = ['Food Items', 'Hygiene Kits', 'Medical Supplies'];
$existingGoodsQuery = $connection->prepare(
    'SELECT goods_name FROM relief_goods WHERE category_id = ? AND goods_name LIKE ?'
);
$insertGood = $connection->prepare(
    'INSERT INTO relief_goods
        (category_id, goods_name, description, unit_of_measure, quantity_in_stock, reorder_level, expiration_date, source, unit_cost, is_active)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1)'
);
$addedByCategory = [];
$skippedByCategory = [];

try {
    $categories = $connection->query('SELECT id, category_name FROM relief_categories ORDER BY id')->fetchAll(PDO::FETCH_ASSOC);
    if (!$categories) {
        throw new RuntimeException('No relief goods categories were found.');
    }

    foreach ($categories as $category) {
        $categoryId = (int) $category['id'];
        $categoryName = $category['category_name'];
        $namePrefix = 'Sample ' . $categoryName . ' ';
        $existingGoodsQuery->execute([$categoryId, $namePrefix . '%']);
        $existingNames = array_fill_keys($existingGoodsQuery->fetchAll(PDO::FETCH_COLUMN), true);
        $added = 0;
        $skipped = 0;

        for ($index = 1; $index <= 1000; $index++) {
            $goodsName = $namePrefix . str_pad((string) $index, 4, '0', STR_PAD_LEFT);
            if (isset($existingNames[$goodsName])) {
                $skipped++;
                continue;
            }

            $isLowStockExample = $index <= 10;
            $isNearExpiryExample = in_array($categoryName, $perishableCategories, true) && $index <= 10;
            $quantity = $isLowStockExample ? 2 + ($index % 7) : 50 + ($index % 451);
            $expirationDate = $isNearExpiryExample
                ? date('Y-m-d', strtotime('+' . (10 + ($index % 20)) . ' days'))
                : null;
            $description = $isLowStockExample
                ? 'Sample inventory record marked as low stock.'
                : 'Generated sample inventory record.';
            if ($isNearExpiryExample) {
                $description .= ' Near-expiry sample for inventory monitoring.';
            }

            $insertGood->execute([
                $categoryId,
                $goodsName,
                $description,
                $unitByCategory[$categoryName] ?? 'piece',
                $quantity,
                10,
                $expirationDate,
                'Sample inventory',
                0.00
            ]);
            $added++;
        }

        $addedByCategory[$categoryName] = $added;
        $skippedByCategory[$categoryName] = $skipped;
    }

    $connection->commit();
} catch (Throwable $error) {
    if ($connection->inTransaction()) {
        $connection->rollBack();
    }
    fwrite(STDERR, 'Seeding failed; transaction rolled back: ' . $error->getMessage() . "\n");
    exit(1);
}

foreach ($addedByCategory as $categoryName => $added) {
    printf("%s: added %d, already present %d\n", $categoryName, $added, $skippedByCategory[$categoryName]);
}
printf("Completed sample goods seeding across %d categories.\n", count($addedByCategory));
