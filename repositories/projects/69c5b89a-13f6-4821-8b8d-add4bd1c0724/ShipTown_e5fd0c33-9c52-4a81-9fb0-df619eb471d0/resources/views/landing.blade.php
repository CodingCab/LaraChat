@extends('layouts.auth')

@section('title', t('Welcome to ShipTown'))

@section('body_class', 'landing-page')

@section('css')
<style>
    body.landing-page {
        background: linear-gradient(135deg, #f9fbff 25%, #ffffff 100%);
        background-image: repeating-linear-gradient(135deg, transparent, transparent 40px, rgba(0,0,0,0.01) 40px, rgba(0,0,0,0.01) 80px);
    }

    .landing-page h1 {
        font-size: 3rem;
        letter-spacing: 0.5px;
    }

    .landing-page p.lead {
        max-width: 600px;
        margin-left: auto;
        margin-right: auto;
        color: #6c757d;
        line-height: 1.6;
    }

    .landing-page .btn-primary {
        background-color: #007bff;
        border-color: #007bff;
        color: #fff;
        padding: 0.75rem 1.25rem;
        border-radius: 0.3rem;
    }

    .landing-page .btn-outline-primary {
        background-color: transparent;
        border-color: #007bff;
        color: #007bff;
        padding: 0.75rem 1.25rem;
        border-radius: 0.3rem;
    }

    .landing-page .feature-card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .landing-page .feature-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }

    .landing-page .form-control {
        padding: 0.75rem;
        border-radius: 0.3rem;
        border: 1px solid #ced4da;
    }

    .landing-page .form-control:focus {
        border-color: #007bff;
        box-shadow: none;
    }
</style>
@endsection

@section('content')
<nav class="navbar navbar-light bg-white border-bottom">
    <div class="container d-flex justify-content-between">
        <span class="navbar-brand">{{ t('ShipTown') }}</span>
        <a href="{{ route('login') }}" class="btn btn-outline-primary">{{ t('Login') }}</a>
    </div>
</nav>

<div class="container my-5">
    <div class="row justify-content-center text-center">
        <div class="col-md-8">
            <h1 class="display-4 mb-4">{{ t('Automate your business with ShipTown') }}</h1>
            <p class="lead mb-4">{{ t('Streamline your warehouse, shipping and retail operations in one intuitive solution.') }}</p>
            <a href="{{ route('login') }}" class="btn btn-primary btn-lg">{{ t('Start Now') }}</a>
            <a href="mailto:sales@shiptown.com" class="btn btn-outline-primary btn-lg ml-2">{{ t('Contact Us') }}</a>
        </div>
    </div>

    <hr class="my-5">

    <div class="row text-center">
        <div class="col-md-4 mb-4 feature-card">
            <h3>{{ t('Real-Time Inventory') }}</h3>
            <p>{{ t('Keep stock levels accurate across all channels.') }}</p>
        </div>
        <div class="col-md-4 mb-4 feature-card">
            <h3>{{ t('Powerful Analytics') }}</h3>
            <p>{{ t('Make informed decisions with comprehensive reports.') }}</p>
        </div>
        <div class="col-md-4 mb-4 feature-card">
            <h3>{{ t('Seamless Shipping') }}</h3>
            <p>{{ t('Print labels and track shipments with ease.') }}</p>
        </div>
    </div>

    <hr class="my-5">

    <h2 class="mb-4 text-center">{{ t('Pricing') }}</h2>
    <div class="table-responsive mb-5">
        <table class="table table-bordered text-center">
            <thead>
                <tr>
                    <th></th>
                    <th>{{ t('Free') }}</th>
                    <th>
                        {{ t('Business') }}<br>
                        <small>€149 {{ t('per month / user') }}</small>
                    </th>
                    <th>
                        {{ t('Enterprise') }}<br>
                        <small>€299 {{ t('per month / user') }}</small>
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <th>{{ t('Orders per month') }}</th>
                    <td>50</td>
                    <td>{{ t('Unlimited') }}</td>
                    <td>{{ t('Unlimited') }}</td>
                </tr>
                <tr>
                    <th>{{ t('Dedicated Account Manager') }}</th>
                    <td>✖</td>
                    <td>✔</td>
                    <td>✔</td>
                </tr>
                <tr>
                    <th>{{ t('Number of products') }}</th>
                    <td>{{ t('Unlimited') }}</td>
                    <td>{{ t('Unlimited') }}</td>
                    <td>{{ t('Unlimited') }}</td>
                </tr>
                <tr>
                    <th>{{ t('Inventory management') }}</th>
                    <td>✔</td>
                    <td>✔</td>
                    <td>✔</td>
                </tr>
                <tr>
                    <th>{{ t('Orders management') }}</th>
                    <td>✔</td>
                    <td>✔</td>
                    <td>✔</td>
                </tr>
                <tr>
                    <th>{{ t('Platform integrations') }}</th>
                    <td>{{ t('Unlimited') }}</td>
                    <td>{{ t('Unlimited') }}</td>
                    <td>{{ t('Unlimited') }}</td>
                </tr>
                <tr>
                    <th>{{ t('Courier integrations') }}</th>
                    <td>{{ t('Unlimited') }}</td>
                    <td>{{ t('Unlimited') }}</td>
                    <td>{{ t('Unlimited') }}</td>
                </tr>
                <tr>
                    <th>{{ t('Point Of Sale') }}</th>
                    <td>✔</td>
                    <td>✔</td>
                    <td>✔</td>
                </tr>
                <tr>
                    <th>{{ t('Number of warehouses') }}</th>
                    <td>{{ t('Unlimited') }}</td>
                    <td>{{ t('Unlimited') }}</td>
                    <td>{{ t('Unlimited') }}</td>
                </tr>
                <tr>
                    <th>{{ t('Number of physical stores') }}</th>
                    <td>{{ t('Unlimited') }}</td>
                    <td>{{ t('Unlimited') }}</td>
                    <td>{{ t('Unlimited') }}</td>
                </tr>
                <tr>
                    <th>{{ t('Advanced Automations') }}</th>
                    <td>✔</td>
                    <td>✔</td>
                    <td>✔</td>
                </tr>
                <tr>
                    <th>{{ t('Hardware Integrations') }}</th>
                    <td>✔</td>
                    <td>✔</td>
                    <td>✔</td>
                </tr>
                <tr>
                    <th>{{ t('Orders packing module') }}</th>
                    <td>✔</td>
                    <td>✔</td>
                    <td>✔</td>
                </tr>
                <tr>
                    <th>{{ t('Real-time stock synchronization') }}</th>
                    <td>✔</td>
                    <td>✔</td>
                    <td>✔</td>
                </tr>
                <tr>
                    <th>{{ t('Multi-warehouse') }}</th>
                    <td>✔</td>
                    <td>✔</td>
                    <td>✔</td>
                </tr>
                <tr>
                    <th>{{ t('Advanced API Integrations') }}</th>
                    <td>✔</td>
                    <td>✔</td>
                    <td>✔</td>
                </tr>
                <tr>
                    <th>{{ t('Invoicing software integrations') }}</th>
                    <td>✔</td>
                    <td>✔</td>
                    <td>✔</td>
                </tr>
                <tr>
                    <th>{{ t('SMS Shipping Notifications') }}</th>
                    <td>✔</td>
                    <td>✔</td>
                    <td>✔</td>
                </tr>
                <tr>
                    <th>{{ t('Picklists powered by Autopilot AI') }}</th>
                    <td>✔</td>
                    <td>✔</td>
                    <td>✔</td>
                </tr>
                <tr>
                    <th>{{ t('Packlists powered by Autopilot AI') }}</th>
                    <td>✔</td>
                    <td>✔</td>
                    <td>✔</td>
                </tr>
                <tr>
                    <th>{{ t('Multi-location order shipping') }}</th>
                    <td>✔</td>
                    <td>✔</td>
                    <td>✔</td>
                </tr>
                <tr>
                    <th>{{ t('Shipping Labels Customization') }}</th>
                    <td>✔</td>
                    <td>✔</td>
                    <td>✔</td>
                </tr>
                <tr>
                    <th>{{ t('Transfers, restocking, stocktakes') }}</th>
                    <td>✔</td>
                    <td>✔</td>
                    <td>✔</td>
                </tr>
                <tr>
                    <th>{{ t('Stocktake suggestions') }}</th>
                    <td>✔</td>
                    <td>✔</td>
                    <td>✔</td>
                </tr>
                <tr>
                    <th>{{ t('Smart Shelf Labels') }}</th>
                    <td>✔</td>
                    <td>✔</td>
                    <td>✔</td>
                </tr>
                <tr>
                    <th>{{ t('Advanced reports') }}</th>
                    <td>✔</td>
                    <td>✔</td>
                    <td>✔</td>
                </tr>
                <tr>
                    <th>{{ t('User permissions') }}</th>
                    <td>✔</td>
                    <td>✔</td>
                    <td>✔</td>
                </tr>
                <tr>
                    <th>{{ t('Private Cloud Hosting') }}</th>
                    <td>✖</td>
                    <td>✖</td>
                    <td>✔</td>
                </tr>
                <tr>
                    <th>{{ t('Priority Support') }}</th>
                    <td>✖</td>
                    <td>✖</td>
                    <td>✔</td>
                </tr>
                <tr>
                    <th>{{ t('Quarterly Staff Training Webinars') }}</th>
                    <td>✖</td>
                    <td>✖</td>
                    <td>✔</td>
                </tr>
                <tr>
                    <th>{{ t('Quarterly Strategy Calls with Logistics & Product Experts') }}</th>
                    <td>✖</td>
                    <td>✖</td>
                    <td>✔</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="row text-center mb-5">
        <div class="col-md-3 mb-4 feature-card">
            <h3>{{ t('300+ Integrations') }}</h3>
            <p>{{ t('Seamlessly connect with 300+ e-commerce platforms, couriers, and tools to streamline your workflow.') }}</p>
        </div>
        <div class="col-md-3 mb-4 feature-card">
            <h3>{{ t('2.5M+ Orders Shipped') }}</h3>
            <p>{{ t('Over 2.5 million orders shipped seamlessly with ShipTown—fast, reliable, and efficient fulfillment.') }}</p>
        </div>
        <div class="col-md-3 mb-4 feature-card">
            <h3>{{ t('28% Faster Order Packing') }}</h3>
            <p>{{ t('On average, our customers pack orders 28% faster with ShipTown') }}</p>
        </div>
        <div class="col-md-3 mb-4 feature-card">
            <h3>{{ t('32% Efficiency Boost') }}</h3>
            <p>{{ t('Our customers see an average 32% increase in efficiency') }}</p>
        </div>
    </div>

    <hr class="my-5">

    <div class="row justify-content-center">
        <div class="col-md-8">
            <h2 class="mb-4 text-center">{{ t('Contact Us') }}</h2>
            <form action="mailto:support@myshiptown.com" method="post" enctype="text/plain">
                <div class="form-group mb-3">
                    <label for="contact-name">{{ t('Your Name') }}</label>
                    <input type="text" name="name" id="contact-name" class="form-control" required>
                </div>
                <div class="form-group mb-3">
                    <label for="contact-email">{{ t('Your Email') }}</label>
                    <input type="email" name="email" id="contact-email" class="form-control" required>
                </div>
                <div class="form-group mb-3">
                    <label for="contact-message">{{ t('Your Message') }}</label>
                    <textarea name="message" id="contact-message" rows="5" class="form-control" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">{{ t('Send Message') }}</button>
            </form>
        </div>
    </div>
</div>
<footer class="bg-light border-top py-3 mt-5">
    <div class="container text-center">
        <a href="{{ asset('release-notes/index.html') }}">{{ t('Release Notes') }}</a>
    </div>
</footer>
@endsection
