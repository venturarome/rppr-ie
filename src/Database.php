<?php

require_once 'Property.php';

class Database
{
    private static ?Database $instance = null;

    private function __construct(
        //private SQLite3 $connection = new SQLite3(filename: ':memory:')   <-- Faster, but non-persistent.
        private SQLite3 $connection = new SQLite3(filename: '../data/rppr-ie-db.sqlite')
    ) {
        $connection->exec(
            "CREATE TABLE IF NOT EXISTS property (
                id INTEGER PRIMARY KEY,
                date_y INT NOT NULL,
                date_m INT NOT NULL,
                date_d INT NOT NULL,
                address VARCHAR(255) NOT NULL,
                county VARCHAR(64) NOT NULL,
                eircode VARCHAR(8),
                price INTEGER NOT NULL,
                type VARCHAR(16) NOT NULL)"
        );
    }

    public static function get(): Database
    {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function insert(Property $property)
    {
        $stmt = $this->connection->prepare(
            query: "INSERT INTO property (date_y, date_m, date_d, address, county, eircode, price, type) VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->bindValue(1, $property->date->format('Y'), SQLITE3_INTEGER);
        $stmt->bindValue(2, $property->date->format('m'), SQLITE3_INTEGER);
        $stmt->bindValue(3, $property->date->format('d'), SQLITE3_INTEGER);
        $stmt->bindValue(4, $property->address);
        $stmt->bindValue(5, $property->county);
        $stmt->bindValue(6, $property->eircode);
        $stmt->bindValue(7, $property->price, SQLITE3_INTEGER);
        $stmt->bindValue(8, $property->type);
        $stmt->execute();
    }

    public function dataPerYear(): array
    {
        $query = <<<SQL
        SELECT p.date_y AS year,
               ROUND(AVG(p.price), 2) AS avg_price,
               COUNT(*) AS sales,
               ROUND(100.0 * COUNT(CASE WHEN p.type = 'new' THEN 1 END) / COUNT(*), 2) AS new_percentage
        FROM property p
        GROUP BY year
        ORDER BY year
        SQL;

        $years = [];
        $avgPrices = [];
        $sales = [];
        $newPercentage = [];
        $secondHandPercentage = [];

        $result = $this->connection->query($query);
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $years[] = $row['year'];
            $avgPrices[] = (float)$row['avg_price'];
            $sales[] = (int)$row['sales'];
            $newPercentage[] = (float)$row['new_percentage'];
            $secondHandPercentage[] = 100-(float)$row['new_percentage'];
        }

        return [
            'years' => $years,
            'avg_prices' => $avgPrices,
            'sales' => $sales,
            'newPercentage' => $newPercentage,
            'secondHandPercentage' => $secondHandPercentage,
        ];
    }

    public function dataPerCounty(): array
    {
        $query = <<<SQL
        SELECT p.county AS county,
               ROUND(AVG(p.price), 2) AS avg_price,
               COUNT(*) AS sales
        FROM property p
        GROUP BY county
        ORDER BY avg_price
        SQL;

        $counties = [];
        $avgPrices = [];
        $sales = [];
        $result = $this->connection->query($query);
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $counties[] = $row['county'];
            $avgPrices[] = (float)$row['avg_price'];
            $sales[] = (int)$row['sales'];
        }

        return [
            'counties' => $counties,
            'avg_prices' => $avgPrices,
            'sales' => $sales
        ];
    }

    public function dataPerYearAndCounty(): array
    {
        $query = <<<SQL
        SELECT p.date_y AS year,
               p.county AS county,
               ROUND(AVG(p.price), 2) AS avg_price,
               COUNT(*) AS sales,
               ROUND(100.0 * COUNT(CASE WHEN p.type = 'new' THEN 1 END) / COUNT(*), 2) AS new_percentage
        FROM property p
        GROUP BY county, year
        ORDER BY county, year
        SQL;

        $years = [];
        $avgPrices = [];
        $sales = [];
        $newPercentage = [];
        $secondHandPercentage = [];

        $result = $this->connection->query($query);
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $years[] = $row['year'];

            $county = $row['county'];
            $counties[] = $county;
            $avgPrices[$county][] = (float)$row['avg_price'];
            $sales[$county][] = (int)$row['sales'];
            $newPercentage[$county][] = (float)$row['new_percentage'];
            $secondHandPercentage[$county][] = 100-(float)$row['new_percentage'];
        }

        $years = array_unique($years);
        $counties = array_values(array_unique($counties));

        return [
            'years' => $years,
            'counties' => $counties,
            'avg_prices' => $avgPrices,
            'sales' => $sales,
            'newPercentage' => $newPercentage,
            'secondHandPercentage' => $secondHandPercentage,
        ];
    }

    public function dataPerMonth(): array
    {
        $query = <<<SQL
        SELECT p.date_m AS month,
               ROUND(AVG(p.price), 2) AS avg_price,
               COUNT(*) AS sales,
               ROUND(100.0 * COUNT(CASE WHEN p.type = 'new' THEN 1 END) / COUNT(*), 2) AS new_percentage
        FROM property p
        GROUP BY month
        ORDER BY month
        SQL;

        $months = [];
        $avgPrices = [];
        $sales = [];
        $newPercentage = [];
        $secondHandPercentage = [];

        $result = $this->connection->query($query);
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $months[] = $row['month'];
            $avgPrices[] = (float)$row['avg_price'];
            $sales[] = (int)$row['sales'];
            $newPercentage[] = (float)$row['new_percentage'];
            $secondHandPercentage[] = 100-(float)$row['new_percentage'];
        }

        return [
            'months' => $months,
            'avg_prices' => $avgPrices,
            'sales' => $sales,
            'newPercentage' => $newPercentage,
            'secondHandPercentage' => $secondHandPercentage,
        ];
    }

    public function dataPerDublinDistrict(): array
    {
        $query = <<<SQL
        SELECT DISTINCT SUBSTR(p.eircode, 0, 4) as district,
                ROUND(AVG(p.price), 2) as avg_price,
                COUNT(p.id) as sales,
                ROUND(100.0 * COUNT(CASE WHEN p.type = 'new' THEN 1 END) / COUNT(*), 2) AS new_percentage
        FROM property p
        WHERE SUBSTR(p.eircode, 1, 3) BETWEEN 'D01' AND 'D24' AND p.date_y > 2020
        GROUP BY district
        ORDER BY district;
        SQL;

        $districts = [];
        $avgPrices = [];
        $sales = [];
        $newPercentage = [];
        $secondHandPercentage = [];

        $result = $this->connection->query($query);
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $districts[] = $row['district'];
            $avgPrices[] = (float)$row['avg_price'];
            $sales[] = (int)$row['sales'];
            $newPercentage[] = (float)$row['new_percentage'];
            $secondHandPercentage[] = 100-(float)$row['new_percentage'];
        }

        return [
            'districts' => $districts,
            'avg_prices' => $avgPrices,
            'sales' => $sales,
            'newPercentage' => $newPercentage,
            'secondHandPercentage' => $secondHandPercentage,
        ];
    }

    public function dataPerDublinSide(): array
    {
        $query = <<<SQL
        SELECT CASE WHEN SUBSTR(p.eircode, 1, 3) BETWEEN 'D01' AND 'D06' THEN 'center'
                    WHEN CAST(SUBSTR(p.eircode, 3, 1) AS INT) % 2 = 0 THEN 'south'
                    ELSE 'north' END as side,
                ROUND(AVG(p.price), 2) as avg_price,
                COUNT(p.id) as sales,
                ROUND(100.0 * COUNT(CASE WHEN p.type = 'new' THEN 1 END) / COUNT(*), 2) AS new_percentage
        FROM property p
        WHERE SUBSTR(p.eircode, 1, 3) BETWEEN 'D01' AND 'D24' AND p.date_y > 2020
        GROUP BY side
        ORDER BY CASE side
            WHEN 'north' THEN 1
            WHEN 'center' THEN 2
            ELSE 3
        END;;
        SQL;

        $sides = [];
        $avgPrices = [];
        $sales = [];
        $newPercentage = [];
        $secondHandPercentage = [];

        $result = $this->connection->query($query);
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $sides[] = $row['side'];
            $avgPrices[] = (float)$row['avg_price'];
            $sales[] = (int)$row['sales'];
            $newPercentage[] = (float)$row['new_percentage'];
            $secondHandPercentage[] = 100-(float)$row['new_percentage'];
        }

        return [
            'sides' => $sides,
            'avg_prices' => $avgPrices,
            'sales' => $sales,
            'newPercentage' => $newPercentage,
            'secondHandPercentage' => $secondHandPercentage,
        ];
    }
}