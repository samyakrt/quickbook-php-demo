<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <style>
        body {
            background:#fff;
            
        }

        .container {
            max-width:1024px;
            /* width : 80%; */
            /* margin: 0 auto; */
            padding : 0 15px;

        }
    </style>
</head>
<body>

<div class="container">

    {{-- {{dd(session('inovice'))}} --}}
    
    
    @if (!!session('tokenData'))
        <pre>
            {{\Carbon\Carbon::parse(session('tokenData')->expires_in)}}
            {{\Carbon\Carbon::now()}}
        </pre>

        {{-- @if (json_encode(session('tokenData'))->expires_in == now() )
            <b>test</b>
        @endif --}}
        
        {{-- {{dd(session('account'))}} --}}
        
        @if (!session('customer'))
        <div>
            <a href="{{route('quickbooks.customers.create')}}">Create Customer</a>
        </div>
        @else 

        @if (!session('account'))
        <a href="{{route('create-account')}}">Create Account</a>
        @else
        <a href="{{route('quickbooks.create-item')}}">Create Item</a>    
        @if (!session('item'))
            
        @else
          @if (!session('inovice'))
            <div>
                <a href="{{route('quickbooks.create-invoice')}}">Create Invoice</a>
            </div>
          @else
              <div>
                  <a href="{{route('send-invoice')}}">Send Invoice</a>
              </div>
          @endif
        @endif
            
        @endif
        {{-- {{session('invoice')}} --}}
        @endif
    @else 
    <a href="{{route('quick-books.authorize')}}" > Connect with Quick books</a>
    @endif
    
</div>

</body>
</html>