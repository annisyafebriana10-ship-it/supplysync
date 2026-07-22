<div class="row g-2 mt-0 align-items-stretch">
    
    <!-- Economic & Risk Trends (Chart.js) -->
    <div class="col-lg-7 col-md-12 mb-2 mb-lg-0">
        <div class="card-corporate p-3 h-100 shadow-sm d-flex flex-column" style="background: white; border-radius: 12px; border: 1px solid #f3f4f6;">
            <!-- Header & Insight (Lebih Informatif) -->
            <div class="d-flex justify-content-between align-items-start mb-1">
                <div>
                    <h6 class="fw-bold text-dark mb-1" style="font-size: 0.95rem;"><i class="fa-solid fa-chart-column me-2 text-secondary"></i>Risk Factor Breakdown</h6>
                    
                    @php
                        $highestFactor = 'Aman';
                        $insightText = 'Semua metrik dalam batas normal.';
                        $textColor = 'text-success';
                        
                        if(isset($riskData['breakdown'])) {
                            $factors = [
                                'Cuaca Ekstrem' => $riskData['breakdown']['weather'],
                                'Inflasi Ekonomi' => $riskData['breakdown']['inflation'],
                                'Fluktuasi Mata Uang' => $riskData['breakdown']['currency'],
                                'Sentimen Berita' => $riskData['breakdown']['news']
                            ];
                            arsort($factors); 
                            $highestFactor = array_key_first($factors);
                            $highestScore = $factors[$highestFactor];

                            if($highestScore >= 67) {
                                $insightText = "Titik kritis logistik dipicu oleh " . $highestFactor . " (" . $highestScore . ").";
                                $textColor = 'text-danger';
                            } elseif($highestScore >= 34) {
                                $insightText = "Perlu pengawasan ekstra pada sektor " . $highestFactor . ".";
                                $textColor = 'text-warning';
                            }
                        }
                    @endphp

                    <div class="d-flex align-items-center mt-1">
                        <span class="badge bg-light text-dark border me-2" style="font-size: 0.6rem;"><i class="fa-solid fa-lightbulb text-warning me-1"></i>Insight</span>
                        <small class="{{ $textColor }} fw-bold" style="font-size: 0.75rem;">{{ $insightText }}</small>
                    </div>
                </div>
            </div>
            
            <!-- Tempat Chart.js -->
            <div class="w-100 mt-auto" style="height: 180px;">
                <canvas id="riskBreakdownChart"></canvas>
            </div>
        </div>
    </div>

    <!-- News Sentiment Area -->
    <div class="col-lg-5 col-md-12">
        <div class="card-corporate p-3 h-100 shadow-sm d-flex flex-column" style="background: white; border-radius: 12px; border: 1px solid #f3f4f6;">
            
            <!-- Header Berita & Tombol Detail -->
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="fw-bold text-dark mb-0" style="font-size: 0.95rem;"><i class="fa-regular fa-newspaper me-2 text-secondary"></i>Market Sentiment</h6>
                
                @if(count($news) > 2)
                    <button class="btn btn-sm btn-light border shadow-sm rounded-2 d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#newsModal" style="font-size: 0.7rem; padding: 4px 10px;">
                        <i class="fa-solid fa-expand me-1"></i> Detail
                    </button>
                @endif
            </div>
            
            <!-- Kontainer Berita (Menggunakan flex-grow-1 agar mengisi seluruh ruang) -->
            <div class="d-flex flex-column flex-grow-1 gap-2 mt-1">
                @forelse(array_slice($news, 0, 2) as $article)
                    @php
                        if ($article['sentiment'] == 'Positive') {
                            $bgColor = '#f0fdf4'; 
                            $borderColor = '#22c55e';
                            $badgeClass = 'bg-success';
                        } elseif ($article['sentiment'] == 'Negative') {
                            $bgColor = '#fef2f2'; 
                            $borderColor = '#ef4444';
                            $badgeClass = 'bg-danger';
                        } else {
                            $bgColor = '#f9fafb'; 
                            $borderColor = '#9ca3af';
                            $badgeClass = 'bg-secondary';
                        }
                    @endphp

                    <!-- Ditambahkan: flex-fill d-flex flex-column justify-content-center -->
                    <!-- Ini akan memaksa tiap berita memiliki tinggi yang SAMA PERSIS dan membagi ruang 50:50 -->
                    <div class="px-3 py-2 rounded border-start border-3 flex-fill d-flex flex-column justify-content-center" style="background-color: {{ $bgColor }}; border-color: {{ $borderColor }} !important;">
                        <a href="{{ $article['url'] }}" target="_blank" class="text-decoration-none">
                            <p class="mb-1 fw-bold text-dark" style="font-size: 0.8rem; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; line-height: 1.4;">
                                {{ $article['title'] }}
                            </p>
                        </a>
                        <div class="d-flex justify-content-between align-items-center mt-2">
                            <span class="text-muted text-truncate w-75" style="font-size: 0.7rem;"><i class="fa-regular fa-clock me-1"></i> {{ $article['source'] }}</span>
                            <span class="badge {{ $badgeClass }} text-white" style="font-size: 0.55rem; padding: 0.4em 0.6em;">{{ $article['sentiment'] }}</span>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-muted m-auto">
                        <i class="fa-regular fa-folder-open fs-4 mb-2 text-light"></i>
                        <p style="font-size: 0.75rem;">Belum ada sentimen pasar.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL UNTUK MELIHAT SEMUA BERITA -->
<!-- ========================================== -->
<div class="modal fade" id="newsModal" tabindex="-1" aria-labelledby="newsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content" style="border-radius: 12px; border: none;">
            <div class="modal-header border-bottom-0 pb-0">
                <h6 class="modal-title fw-bold" id="newsModalLabel"><i class="fa-regular fa-newspaper me-2" style="color: var(--matcha-500);"></i>Semua Berita Sentimen</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-3">
                    @foreach($news as $article)
                        @php
                            $badgeClass = $article['sentiment'] == 'Positive' ? 'bg-success' : ($article['sentiment'] == 'Negative' ? 'bg-danger' : 'bg-secondary');
                        @endphp
                        <div class="col-md-6">
                            <div class="card h-100 shadow-sm border-0 bg-light p-3 rounded-3">
                                <span class="badge {{ $badgeClass }} text-white mb-2" style="width: fit-content; font-size: 0.6rem;">{{ $article['sentiment'] }}</span>
                                <a href="{{ $article['url'] }}" target="_blank" class="text-decoration-none text-dark fw-bold mb-2" style="font-size: 0.85rem; line-height: 1.4;">
                                    {{ $article['title'] }}
                                </a>
                                <div class="mt-auto pt-2 border-top">
                                    <small class="text-muted" style="font-size: 0.7rem;"><i class="fa-regular fa-building me-1"></i>{{ $article['source'] }}</small>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<!-- PANGGIL LIBRARY CHART.JS -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- LOGIKA MENGGAMBAR GRAFIK -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const ctx = document.getElementById('riskBreakdownChart').getContext('2d');
        const riskData = @json($riskData['breakdown'] ?? null);

        if (riskData) {
            const getDynamicColor = (value) => {
                if (value >= 67) return 'rgba(239, 68, 68, 0.9)'; 
                if (value >= 34) return 'rgba(245, 158, 11, 0.9)'; 
                return 'rgba(16, 185, 129, 0.9)'; 
            };

            const dataValues = [riskData.weather, riskData.inflation, riskData.currency, riskData.news];
            const bgColors = dataValues.map(val => getDynamicColor(val));

            const thresholdLinePlugin = {
                id: 'thresholdLine',
                beforeDraw: (chart) => {
                    const ctx = chart.ctx;
                    const yAxis = chart.scales.y;
                    const xAxis = chart.scales.x;
                    const yPos = yAxis.getPixelForValue(33); 

                    ctx.save();
                    ctx.beginPath();
                    ctx.moveTo(xAxis.left, yPos);
                    ctx.lineTo(xAxis.right, yPos);
                    ctx.lineWidth = 1.5;
                    ctx.strokeStyle = 'rgba(239, 68, 68, 0.6)'; 
                    ctx.setLineDash([5, 5]); 
                    ctx.stroke();
                    
                    ctx.fillStyle = 'rgba(239, 68, 68, 0.8)';
                    ctx.font = '10px "Inter", sans-serif';
                    ctx.fillText('Batas Aman', xAxis.right - 55, yPos - 5);
                    ctx.restore();
                }
            };

            new Chart(ctx, {
                type: 'bar', 
                data: {
                    labels: ['Weather', 'Inflation', 'Currency', 'News'], 
                    datasets: [{
                        data: dataValues,
                        backgroundColor: bgColors,
                        borderRadius: 6,
                        borderSkipped: false,
                        barPercentage: 0.45 
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            grid: {
                                color: '#f3f4f6', 
                                drawBorder: false,
                            },
                            ticks: {
                                font: { size: 10, family: "'Inter', sans-serif" },
                                color: '#9ca3af',
                                stepSize: 33 
                            }
                        },
                        x: {
                            grid: { display: false }, 
                            ticks: {
                                font: { size: 11, weight: '600', family: "'Inter', sans-serif" },
                                color: '#4b5563'
                            }
                        }
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#1f2937',
                            padding: 10,
                            titleFont: { size: 12 },
                            bodyFont: { size: 13, weight: 'bold' },
                            displayColors: false,
                            cornerRadius: 8,
                            callbacks: {
                                label: function(context) {
                                    return 'Skor Risiko: ' + context.raw;
                                }
                            }
                        }
                    }
                },
                plugins: [thresholdLinePlugin] 
            });
        }
    });
</script>