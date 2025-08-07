@extends('layouts.app')

@section('title', __('Settings'))

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-12">
            @if (session('status'))
                <div class="alert alert-success" role="alert">
                    {{ session('status') }}
                </div>
            @endif

            <div class="list-group">
                <a href="{{ route('settings.general') }}" class="setting-list">
                    <div class="setting-icon">
                        <font-awesome-icon icon="cog" class="fa-sm"></font-awesome-icon>
                    </div>
                    <div class="setting-body">
                        <div class="setting-title">{{ t('General') }}</div>
                        <div class="setting-desc">{{ t('View and setting-desc application settings') }}</div>
                    </div>
                </a>

                <a href="{{ route('settings.users') }}" class="setting-list">
                    <div class="setting-icon">
                        <font-awesome-icon icon="users" class="fa-sm"></font-awesome-icon>
                    </div>
                    <div class="setting-body">
                        <div class="setting-title">{{ t('Users') }}</div>
                        <div class="setting-desc">{{ t('Manage users') }}</div>
                    </div>
                </a>

                <a href="{{ route('settings.warehouses') }}" class="setting-list">
                    <div class="setting-icon">
                        <font-awesome-icon icon="warehouse" class="fa-sm"></font-awesome-icon>
                    </div>
                    <div class="setting-body">
                        <div class="setting-title">{{ t('Warehouses') }}</div>
                        <div class="setting-desc">{{ t('Manage warehouses') }}</div>
                    </div>
                </a>

                <a href="{{ route('settings.automations', ['sort' => 'priority,from_status_code,to_status_code']) }}" class="setting-list" dusk="order-automations-link">
                    <div class="setting-icon">
                        <font-awesome-icon icon="magic" class="fas-sm"></font-awesome-icon>
                    </div>
                    <div class="setting-body">
                        <div class="setting-title">{{ t('Order Automations') }}</div>
                        <div class="setting-desc">{{ t('Automate Order Workflows') }}</div>
                    </div>
                </a>

                <a href="{{ route('settings.mail_templates') }}" class="setting-list" id="setting-mail-templates">
                    <div class="setting-icon">
                        <font-awesome-icon icon="envelope-open-text" class="fas-sm"></font-awesome-icon>
                    </div>
                    <div class="setting-body">
                        <div class="setting-title">{{ t('Email Templates') }}</div>
                        <div class="setting-desc">{{ t('Manage Email notification templates') }}</div>
                    </div>
                </a>

                <a href="{{ route('settings.order_statuses') }}" class="setting-list">
                    <div class="setting-icon">
                        <font-awesome-icon icon="box-open" class="fa-sm"></font-awesome-icon>
                    </div>
                    <div class="setting-body">
                        <div class="setting-title">{{ t('Order Statuses') }}</div>
                        <div class="setting-desc">{{ t('Manage order statuses') }}</div>
                    </div>
                </a>

                <a href="{{ route('settings.navigation_menu') }}" class="setting-list">
                    <div class="setting-icon">
                        <font-awesome-icon icon="list-ul" class="fas-sm"></font-awesome-icon>
                    </div>
                    <div class="setting-body">
                        <div class="setting-title">{{ t('Navigation') }}</div>
                        <div class="setting-desc">{{ t('Manage navigation menu items') }}</div>
                    </div>
                </a>

                <a href="{{ route('settings.modules') }}" dusk="goToModulesPage" class="setting-list">
                    <div class="setting-icon">
                        <font-awesome-icon icon="puzzle-piece" class="fa-sm"></font-awesome-icon>
                    </div>
                    <div class="setting-body">
                        <div class="setting-title">{{ t('Modules') }}</div>
                        <div class="setting-desc">{{ t('Manage Modules') }}</div>
                    </div>
                </a>

                <a href="{{ route('settings.api') }}" class="setting-list">
                    <div class="setting-icon">
                        <font-awesome-icon icon="key" class="fa-sm"></font-awesome-icon>
                    </div>
                    <div class="setting-body">
                        <div class="setting-title">{{ t('API') }}</div>
                        <div class="setting-desc">{{ t('View and update application API tokens and OAuth clients') }}</div>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@section('css')
    <style>
        .setting-list{
            border-radius: 1px;
            padding: 0.5rem 1rem;
            background-color: #fff;
            border: 1px solid rgba(0, 0, 0, 0.125);
            width: 100%;
            color: #495057;
            display: flex;
            align-items: flex-start;
            margin-bottom: 5px;
        }

        .setting-list:hover, .setting-list:focus {
            color: #495057;
            text-decoration: none;
            background-color: #f8f9fa;
        }

        .setting-icon{
            padding: 1rem;
            margin-right: 1rem;
            background-color: #f8f9fa;
            border-radius: 0.25rem;
        }

        .setting-icon:hover{
            background-color: unset;
        }

        .setting-title{
            color: #3490dc;
            font-weight: bolder;
            /*font-size: 1rem;*/
            /*line-height: 1.2;*/
            margin-bottom: 2px;
        }

        .setting-desc{
            color: #6c757d;
            font-size: 10pt;
        }
    </style>
@endsection
