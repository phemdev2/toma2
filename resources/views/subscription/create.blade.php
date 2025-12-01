@extends('layouts.app')

@section('content')
<div class="container py-5" style="max-width: 800px; margin: 0 auto; padding: 20px;">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-lg" style="border-radius: 15px; border: none;">
                <div class="card-header" style="background-color:rgb(238, 197, 236); color: white; text-align: center; border-top-left-radius: 15px; border-top-right-radius: 15px;">
                    <h2>Choose Your Subscription Plan</h2>
                </div>
                <div class="card-body" style="padding: 30px; background-color: #f8f9fa;">
                    <form method="POST" action="{{ route('subscription.store') }}" style="font-family: Arial, sans-serif;">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="plan_type" class="form-label" style="font-weight: bold; font-size: 1.1rem;">Select Plan</label>
                            <select name="plan_type" id="plan_type" class="form-select" required style="padding: 10px; font-size: 1rem; width: 100%; border-radius: 8px; border: 1px solid #ced4da;">
                                <option value="">-- Select a Plan --</option>
                                <option value="trial">Trial (15 days)</option>
                                <option value="basic">Basic (30 days)</option>
                                <option value="premium">Premium (365 days)</option>
                            </select>
                        </div>

                        {{-- Optionally, if there are payment fields --}}
                        <div class="mb-3">
                            <label for="payment_method" class="form-label" style="font-weight: bold; font-size: 1.1rem;">Payment Method</label>
                            <select name="payment_method" id="payment_method" class="form-select" required style="padding: 10px; font-size: 1rem; width: 100%; border-radius: 8px; border: 1px solid #ced4da;">
                                <option value="">-- Select Payment Method --</option>
                                <option value="credit_card">Credit Card</option>
                                <option value="paypal">PayPal</option>
                                <option value="bank_transfer">Bank Transfer</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="coupon_code" class="form-label" style="font-weight: bold; font-size: 1.1rem;">Coupon Code (Optional)</label>
                            <input type="text" name="coupon_code" id="coupon_code" class="form-control" placeholder="Enter coupon code" style="padding: 10px; font-size: 1rem; width: 100%; border-radius: 8px; border: 1px solid #ced4da;" />
                        </div>

                        <div class="text-center">
                            <button type="submit" class="btn btn-success btn-lg" style="background-color: #28a745; border-radius: 8px; font-size: 1.2rem; padding: 12px 30px;">Subscribe Now</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
