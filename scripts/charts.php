<?php
require_once '../src/Database.php';

$db = Database::get();

$dataPerYear = $db->dataPerYear();
$dataPerCounty = $db->dataPerCounty();
$dataPerYearAndCounty = $db->dataPerYearAndCounty();
$dataPerMonth = $db->dataPerMonth();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Irish Property Market Trends</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<div class="container my-5">
    <h1 class="text-center mb-4">Irish Property Market Overview</h1>
    <br>
    <div class="row g-4">
        <div class="col-md-6" style="text-align: center;">
            <h2>Time-based charts</h2>
        </div>
        <div class="col-md-6" style="text-align: center;">
            <h2>Location-based charts</h2>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm p-3"><canvas id="avgPricesPerYearChart"></canvas></div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm p-3"><canvas id="avgPricePerCountyChart"></canvas></div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm p-3"><canvas id="salesPerYearChart"></canvas></div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm p-3"><canvas id="salesPerCountyChart"></canvas></div>
        </div>

        <div class="col-md-12"><br></div>

        <div class="col-md-12" style="text-align: center;">
            <h2>Segmented data both by time and location</h2>
        </div>
        <div class="col-md-12">
            <div class="card shadow-sm p-3"><canvas id="avgPricesPerYearAndCountyChart"></canvas></div>
        </div>
        <div class="col-md-12">
            <div class="card shadow-sm p-3"><canvas id="salesPerYearAndCountyChart"></canvas></div>
        </div>

        <div class="col-md-12"><br></div>

        <div class="col-md-12" style="text-align: center;">
            <h2>Misc</h2>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm p-3"><canvas id="ratioNewUsedChart"></canvas></div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm p-3"><canvas id="seasonalityChart"></canvas></div>
        </div>
    </div>
</div>

<script>
    const years = <?= json_encode($dataPerYear['years']) ?>;
    const avgPricesPerYear = <?= json_encode($dataPerYear['avg_prices']) ?>;
    const salesPerYear = <?= json_encode($dataPerYear['sales']) ?>;
    const newPropertyPercentagePerYear = <?= json_encode($dataPerYear['newPercentage']) ?>;
    const secondHandPropertyPercentagePerYear = <?= json_encode($dataPerYear['secondHandPercentage']) ?>;

    const counties = <?= json_encode($dataPerCounty['counties']) ?>;
    const avgPricesPerCounty = <?= json_encode($dataPerCounty['avg_prices']) ?>;
    const salesPerCounty = <?= json_encode($dataPerCounty['sales']) ?>;

    const avgPricesPerYearAndCounty = <?= json_encode($dataPerYearAndCounty['avg_prices']) ?>;
    const salesPerYearAndCounty = <?= json_encode($dataPerYearAndCounty['sales']) ?>;

    const months = <?= json_encode($dataPerMonth['months']) ?>;
    const avgPricesPerMonth = <?= json_encode($dataPerMonth['avg_prices']) ?>;
    const salesPerMonth = <?= json_encode($dataPerMonth['sales']) ?>;

    ///////////////////////
    // LINE GRAPHS
    ///////////////////////
    new Chart(document.getElementById('avgPricesPerYearChart'), {
        type: 'line',
        data: {
            labels: years,
            datasets: [{
                //label: 'Average Price (€)',
                data: avgPricesPerYear,
                borderColor: 'blue',
                backgroundColor: 'rgba(0, 0, 255, 0.1)',
                fill: true,
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Average Property Price by Year'
                },
                legend: {
                    display: false
                },
            },
            scales: {
                y: {
                    beginAtZero: false
                }
            }
        }
    });

    new Chart(document.getElementById('salesPerYearChart'), {
        type: 'line',
        data: {
            labels: years,
            datasets: [{
                data: salesPerYear,
                borderColor: 'blue',
                backgroundColor: 'rgba(0, 0, 255, 0.1)',
                fill: true,
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Total Sales by Year'
                },
                legend: {
                    display: false
                },
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });


    ///////////////////////
    // BAR GRAPHS
    ///////////////////////
    new Chart(document.getElementById('avgPricePerCountyChart'), {
        type: 'bar',
        data: {
            labels: counties,
            datasets: [{
                label: 'Sales Count',
                data: avgPricesPerCounty,
                backgroundColor: 'green'
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Average Property Price by County'
                },
                legend: {
                    display: false
                },
            },
            scales: {
                x: {
                    ticks: {
                        autoSkip: false, // Ensures no labels are skipped
                        maxTicksLimit: counties.length // Ensures all labels are shown
                    }
                },
                y: {
                    beginAtZero: true
                }
            }
        }
    });
    new Chart(document.getElementById('salesPerCountyChart'), {
        type: 'bar',
        data: {
            labels: counties,
            datasets: [{
                label: 'Sales Count',
                data: salesPerCounty,
                backgroundColor: 'green'
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Total Sales by County'
                },
                legend: {
                    display: false
                },
            },
            scales: {
                x: {
                    ticks: {
                        autoSkip: false, // Ensures no labels are skipped
                        maxTicksLimit: counties.length // Ensures all labels are shown
                    }
                },
                y: {
                    beginAtZero: true
                }
            }
        }
    });


    ///////////////////////
    // MULTILINE GRAPHS
    ///////////////////////
    const colors = generateDistinctColors(counties.length);
    function generateDistinctColors(count, saturation = 70, lightness = 50) {
        const colors = [];
        const step = 360 / count;

        for (let i = 0; i < count; i++) {
            const hue = i * step;
            colors.push(`hsl(${hue}, ${saturation}%, ${lightness}%)`);
        }

        return colors;
    }

    new Chart(document.getElementById('avgPricesPerYearAndCountyChart'), {
        type: 'line',
        data: {
            labels: years,
            datasets: Object.entries(avgPricesPerYearAndCounty).map(([county, avgPrice], index) => {
                return {
                    label: county,
                    data: avgPrice,
                    borderColor: colors[index],
                    fill: false,
                    tension: 0.1,
                }
            }),
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Average Property Price by Year and County'
                },
            },
            scales: {
                y: {
                    beginAtZero: false
                }
            }
        }
    });
    new Chart(document.getElementById('salesPerYearAndCountyChart'), {
        type: 'line',
        data: {
            labels: years,
            datasets: Object.entries(salesPerYearAndCounty).map(([county, sales], index) => {
                return {
                    label: county,
                    data: sales,
                    borderColor: colors[index],
                    fill: false,
                    tension: 0.1,
                }
            }),
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Sales by Year and County'
                },
            },
            scales: {
                y: {
                    beginAtZero: false
                }
            }
        }
    });


    ///////////////////////
    // MISC GRAPHS
    ///////////////////////
    new Chart(document.getElementById('ratioNewUsedChart'), {
        type: 'line',
        data: {
            labels: years,
            datasets: [{
                label: 'New developments',
                data: newPropertyPercentagePerYear,
                borderColor: 'blue',
                backgroundColor: 'rgba(0, 0, 255, 0.7)',
                fill: true,
                tension: 0.1
            }, {
                label: 'Second-hand properties',
                data: secondHandPropertyPercentagePerYear,
                borderColor: 'green',
                backgroundColor: 'rgba(0, 255, 0, 0.7)',
                fill: true,
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'New vs. Second-hand Properties'
                },
                // legend: {
                //     display: false
                // },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    stacked: true,
                }
            }
        }
    });

    new Chart(document.getElementById('seasonalityChart'), {
        data: {
            labels: months,
            datasets: [{
                label: 'Sales per month',
                type: 'line',
                yAxisID: 'ySales',
                data: salesPerMonth,
                borderColor: 'blue',
                backgroundColor: 'rgba(0, 0, 255, 0.1)',
                fill: false,
                tension: 0.1
            }, {
                label: 'Average prices per month',
                type: 'bar',
                yAxisID: 'yAvgPrices',
                data: avgPricesPerMonth,
                backgroundColor: 'green'
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Monthly seasonality'
                },
            },
            scales: {
                x: {
                    ticks: {
                        autoSkip: false, // Ensures no labels are skipped
                        maxTicksLimit: months // Ensures all labels are shown
                    }
                },
                ySales: {
                    type: 'linear',
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Sales Count'
                    }
                },
                yAvgPrices: {
                    type: 'linear',
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Average Price (€)'
                    },
                    grid: {
                        drawOnChartArea: false // Don't show overlapping grid
                    }
                }
            }
        }
    });
</script>
</body>
</html>