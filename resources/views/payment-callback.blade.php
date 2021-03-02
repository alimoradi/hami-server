<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Laravel</title>

        <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-BmbxuPwQa2lc/FVzBcNJ7UAyJxM6wuqIj61tLrc4wSX0szH/Ev+nYRRuWlolflfl" crossorigin="anonymous">
    <link href="http://cdn.persianfort.ir/Fonts/iransans/iransans.css" rel="stylesheet" integrity="sha384-BmbxuPwQa2lc/FVzBcNJ7UAyJxM6wuqIj61tLrc4wSX0szH/Ev+nYRRuWlolflfl" crossorigin="anonymous">

    <style>
        body{
            font-family: iransans;
            direction:rtl;
        }
        .gree{
            color:green;
        }
        .red{
            color:red;
        }
    </style>
    </head>
    <body>
        <div class="container-fluid">
            <div class="mt-2 row justify-content-center">
                <div class="col col-md-8 col-12 col-sm-10 mt-3">
                    <div class="card text-end">
                        <div class="card-body">
                            
                                @if($success)
                                    <h5 class="card-title green">
                                        تراکنش موفق
                                    </h5>
                                @else 
                                    <h5 class="card-title red">
                                        تراکنش ناموفق
                                    </h5>
                                @endIf
                            
                            @if($success)  
                                <p class="card-text"><span>شماره ارجاع: </span> <mark>{{$refId}}</mark></p>
                            @else
                                <p class="card-text">
                                    متاسفانه تأیید پرداخت با مشکل روبرو شده است. در صورت کم شدن اعتبار از حساب بانکی شما، نهایتا در ۷۲ ساعت، این مبلغ به حساب شما بازخواهد گشت.
                                </p>
                            @endif

                          <a href="hamiline://launch" class="btn btn-primary">بازگشت به حامی لاین</a>
                        </div>
                      </div>
                </div>
            </div>
            
        </div>
        
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/js/bootstrap.bundle.min.js" integrity="sha384-b5kHyXgcpbZJO/tY9Ul7kGkf1S0CWuKcCD38l8YkeH8z8QjE0GmW1gYU5S9FOnJ0" crossorigin="anonymous"></script>

    </body>
</html>
