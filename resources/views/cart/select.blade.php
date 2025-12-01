<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Store Selection</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f4f4f4;
            margin: 0;
        }
        .container {
            display: flex;
            flex-wrap: wrap; /* Allow wrapping for vertical layout */
            justify-content: center; /* Center cards */
            width: 100%;
            max-width: 1200px; /* Adjust as needed */
            padding: 0 20px;
        }
        .card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            width: 200px; /* Fixed width for cards */
            text-align: center;
            cursor: pointer;
            transition: transform 0.2s;
            margin: 10px; /* Space between cards */
        }
        .card:hover {
            transform: scale(1.05);
        }
        @media (min-width: 768px) {
            .container {
                flex-direction: row; /* Horizontal layout on larger screens */
            }
        }
        @media (max-width: 767px) {
            .container {
                flex-direction: column; /* Vertical layout on smaller screens */
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <form method="GET" action="{{ route('cart.index') }}">
            <input type="hidden" name="store_id" id="store_id" value="">
            <div class="card" onclick="selectStore(1)">
                <h3>Store 1</h3>
                <p>Description for Store 1</p>
            </div>
            <div class="card" onclick="selectStore(2)">
                <h3>Store 2</h3>
                <p>Description for Store 2</p>
            </div>
            <div class="card" onclick="selectStore(3)">
                <h3>Store 3</h3>
                <p>Description for Store 3</p>
            </div>
            <div class="card" onclick="selectStore(4)">
                <h3>Store 4</h3>
                <p>Description for Store 4</p>
            </div>
        </form>
    </div>

    <script>
        function selectStore(storeId) {
            document.getElementById('store_id').value = storeId;
            document.forms[0].submit();
        }
    </script>
</body>
</html>