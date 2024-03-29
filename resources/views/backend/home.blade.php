@extends('backend.layouts.app')

@section('content')

    <style>
        .col-md-3.col-xl-3 {
            display: inline-grid;
        }
    </style>
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="alert alert-info">
                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">&times;</button>
                    Chào mừng bạn quay trở lại, <strong>{{Auth::guard('backend')->user()->name}}.</strong>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12 col-xl-12">

                <div class="card card-success">
                    <div class="card-header">
                        <h3 class="card-title">Thời tiết hôm nay</h3>
                    </div>
                    <div class="card-body">
                        <?php
                        $apiKey = "6334eaca94312c6d55c44c89426aec4c";
                        $cityId = "1581130";
                        $googleApiUrl = "http://api.openweathermap.org/data/2.5/weather?id=" . $cityId . "&lang=en&units=metric&APPID=" . $apiKey;

                        $ch = curl_init();

                        curl_setopt($ch, CURLOPT_HEADER, 0);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                        curl_setopt($ch, CURLOPT_URL, $googleApiUrl);
                        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                        curl_setopt($ch, CURLOPT_VERBOSE, 0);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                        $response = curl_exec($ch);

                        curl_close($ch);
                        $data = json_decode($response);
                        $currentTime = time();
                        ?>
                        <div class="report-container">
                            <style>
                                .report-container {
                                    border: #E0E0E0 1px solid;
                                    padding: 20px 20px 100px 20px;
                                    border-radius: 2px;
                                    margin: 0 auto;
                                    color: #1f1f1a;
                                    @switch($data->weather[0]->icon)
                                    @case('01d')          background: url({{asset('img/w_clear_sky.gif')}});
                                    @case('01n')          background: url({{asset('img/w_clear_sky.gif')}});
                                    @case('02d')          background: url({{asset('img/w_few_cloud.gif')}});
                                    @case('02n')          background: url({{asset('img/w_few_cloud.gif')}});
                                    @case('03d')          background: url({{asset('img/w_scattered_clouds.gif')}});
                                    @case('03n')          background: url({{asset('img/w_scattered_clouds.gif')}});
                                    @case('04d')          background: url({{asset('img/w_broken_clouds.gif')}});
                                    @case('04n')          background: url({{asset('img/w_broken_clouds.gif')}});
                                    @case('09d')          background: url({{asset('img/w_shower_rain.gif')}});
                                    @case('09n')          background: url({{asset('img/w_shower_rain.gif')}});
                                    @case('10d')          background: url({{asset('img/w_rain.gif')}});
                                    @case('10n')          background: url({{asset('img/w_rain.gif')}});
                                    @case('11d')          background: url({{asset('img/w_thunder_storm.gif')}});
                                    @case('11n')          background: url({{asset('img/w_thunder_storm.gif')}});
                                    @case('13d')          background: url("https://images.techhive.com/images/article/2017/05/cloud_blueprint_schematic-100722515-large.jpg?auto=webp");
                                    @case('13n')          background: url("https://images.techhive.com/images/article/2017/05/cloud_blueprint_schematic-100722515-large.jpg?auto=webp");
                                    @case('50d')          background: url({{asset('img/w_mist.gif')}});
                                    @case('50n')          background: url({{asset('img/w_mist.gif')}});

                                @endswitch


                                }
                            </style>
                            <div class="row" style="color: darkslategrey">
                                <div class="col-md-6">
                                    <h2 style="margin-bottom: 14px"><?php echo $data->name; ?></h2>
                                    <div class="time">
                                        <div><?php echo date("l g:i a", $currentTime); ?></div>
                                        <div><?php echo date("jS F, Y", $currentTime); ?></div>
                                        <div><?php echo ucwords($data->weather[0]->description); ?></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="weather-forecast">
                                        <img
                                            src="http://openweathermap.org/img/w/<?php echo $data->weather[0]->icon; ?>.png"
                                            class="weather-icon"/><br> <?php echo $data->main->temp_max; ?>°C -
                                        <span
                                            class="min-temperature"><?php echo $data->main->temp_min; ?>°C</span>
                                    </div>
                                    <div class="time">
                                        <div>Độ ẩm: <?php echo $data->main->humidity; ?> %</div>
                                        <div>Sức gió: <?php echo $data->wind->speed; ?> km/h</div>
                                    </div>
                                </div>


                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

@endsection
