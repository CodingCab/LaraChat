<div class="widget-tools-container">
    <font-awesome-icon icon="question-circle" name="orders-help-icon"></font-awesome-icon>
    <template>
        <tippy to="orders-help-icon" arrow>
            <p>
                Total number of orders with following status code:<br>
                - processing
            </p>
        </tippy>
    </template>
</div>
    <div class="card text-center">
        <div class="card-body">
        <h4 class="card-title">PENDING ORDERS</h4>
        <h5 class="card-title"></h5>
        <h2 class="card-text"><strong>{{ $count }}</strong></h2>
    </div>
</div>
