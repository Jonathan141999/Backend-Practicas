<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Productos más Vendidos</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css"
          integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm"
          crossorigin="anonymous">
    <style>
        @page{
            margin: 0cm 0cm;
            font-size: 1em;
        }
        body{
            margin-top: 3cm;
        }
        header{
            position: fixed;
            top: 0cm;
            left: 0cm;
            right: 0cm;
            height: 2cm;
            background-color: #1d68a7;
            color: #ffffff;
            text-align: center;
            line-height: 30px;
        }
    </style>
</head>
<body>
<header>
    <h3><strong>Reporte de InnerSet Store</strong></h3>
</header>
<div class="container">
    <h4 style="text-align: center">Reporte de Productos más Vendidos</h4>
    <table class="table table-sm table-bordered">
        <thead class="thead-light">
        <tr>
            <th scope="col">N°</th>
            <th scope="col">Producto</th>
            <th scope="col">Cantidad Total</th>
            <th scope="col">Total comprado</th>
        </tr>
        </thead>
        <tbody>
        @foreach($quantity as $quant)
            <tr>
                <th scope="row">{{$quant->product_id}}</th>
                @foreach($products as $product)
                    @if ($product->id === $quant->product_id)
                        <td>{{$product->name}}</td>
                    @endif
                @endforeach
                <td>{{$quant->total_quantity}}</td>
                @foreach($products as $product)
                    @if ($product->id === $quant->product_id)
                        <td>{{($quant->total_quantity * $product->price)}}</td>
                    @endif
                @endforeach
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
</body>
</html>


