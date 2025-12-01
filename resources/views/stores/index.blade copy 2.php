@extends('layouts.app')

@section('title', 'Select Store')

@section('content')
    <div class="container">
        <h1 class="my-4">Select a Store</h1>
        <form method="GET" action="{{ route('cart.index') }}">
            <input type="hidden" name="store_id" id="store_id" value="">
            <div class="row">
                @foreach($stores as $store)
                    <div class="col-md-3 mb-3">
                        <div class="card" onclick="selectStore({{ $store->id }})">
                            <div class="card-body">
                                <h5 class="card-title">{{ $store->name }}</h5>
                                <p class="card-text">{{ $store->description }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </form>
    </div>

    <script>
        function selectStore(storeId) {
            document.getElementById('store_id').value = storeId;
            document.forms[0].submit();
        }
    </script>
@endsection
