<header class="header b-b clearfix">
        <div class="panel-body">
            <div class="row">
                <div class="col-md-12">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th width="15%">Time</th>
                                <th width="10%">Component</th>
                                <th width="10%">Type</th>
                                <th>Event</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>2020-07-21 03:02:43</td>
                                <td>SpiderFoot</td>
                                <td>STATUS</td>
                                <td>Running Correlation rules ...</td>
                            </tr>
                            <tr>
                                <td>2020-07-21 03:02:43</td>
                                <td>SpiderFoot</td>
                                <td>STATUS</td>
                                <td>Running Correlation rules ...</td>
                            </tr>
                            <tr>
                                <td>2020-07-21 03:02:43</td>
                                <td>SpiderFoot</td>
                                <td>STATUS</td>
                                <td>Running Correlation rules ...</td>
                            </tr>
                            <tr>
                                <td>2020-07-21 03:02:43</td>
                                <td>SpiderFoot</td>
                                <td>STATUS</td>
                                <td>Running Correlation rules ...</td>
                            </tr>
                            <tr>
                                <td>2020-07-21 03:02:43</td>
                                <td>SpiderFoot</td>
                                <td>STATUS</td>
                                <td>Running Correlation rules ...</td>
                            </tr>
                            <tr class="bg-danger">
                                <td>2020-07-21 03:02:43</td>
                                <td>SpiderFoot</td>
                                <td>ERROR</td>
                                <td>Unable Lorem ipsum dolor sit amet. Correlation rules ...</td>
                            </tr>
                            <tr class="bg-danger">
                                <td>2020-07-21 03:02:43</td>
                                <td>SpiderFoot</td>
                                <td>ERROR</td>
                                <td>Unable Lorem ipsum dolor sit amet. Correlation rules ...</td>
                            </tr>
                            <tr class="bg-danger">
                                <td>2020-07-21 03:02:43</td>
                                <td>SpiderFoot</td>
                                <td>ERROR</td>
                                <td>Unable Lorem ipsum dolor sit amet. Correlation rules ...</td>
                            </tr>
                            <tr class="bg-danger">
                                <td>2020-07-21 03:02:43</td>
                                <td>SpiderFoot</td>
                                <td>ERROR</td>
                                <td>Unable Lorem ipsum dolor sit amet. Correlation rules ...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </header>
    
    @push('pagestyle')
    @include('stacks.css.datatables')
    @include('stacks.css.form')
    @endpush
    
    @push('pagescript')
    @include('stacks.js.datatables')
    @include('stacks.js.form')
    
    @endpush