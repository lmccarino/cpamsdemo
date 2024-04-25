let _statspan = 1;
let _dashboardToken = '';

const percentTotalAssistanceAmount = document.getElementById('percent-total-assistance-amount');
let percentTotalAssistanceAmountChart = null;

const percentTotalOverrideAmount = document.getElementById('percent-total-override-amount');
let percentTotalOverrideAmountChart = null;

const percentTotalAssistanceAmountCategory = document.getElementById('percent-total-assistance-amount-category');
let percentTotalAssistanceAmountCategoryChart = null;

const percentRemainingBalance = document.getElementById('percent-remaining-balance');
let percentRemainingBalanceChart = null;

const percentTotalAssistanceBarangay = document.getElementById('percent-total-assistance-barangay');
let percentTotalAssistanceBarangayChart = null;

const percentTotalAssistanceGender = document.getElementById('percent-total-assistance-gender');
let percentTotalAssistanceGenderChart = null;

const percentTotalAssistancePlatform = document.getElementById('percent-total-assistance-platform');
let percentTotalAssistancePlatformChart = null;

const percentTotalAssistanceAge = document.getElementById('percent-total-assistance-age');
let percentTotalAssistanceAgeChart = null;

const counter = {
    id: 'counter',
    beforeDraw( chart, args, options ) {
        const { ctx, chartArea: { top, right, bottom, left, width, height } } = chart;
        let optionSize = options.fontSize.replace('px','');
        ctx.save();
        ctx.font = options.fontSize + ' ' + options.fontFamily;
        ctx.fillStyle = 'center';
        ctx.fillText(options.dataValue, (width/2)-((options.dataValue.length)*(optionSize/4.5)), (top+(height/2)));
        ctx.font = '15px sans-serif';
        ctx.fillText(options.dataTitle, (width/2)-((options.dataTitle.length)*3.8), (top+(height/2))+18);
    }
}

let totalOverride = ($span,$token,$timer=false) => {
    if(percentTotalAssistanceAmountChart != null && !$timer)
        percentTotalAssistanceAmountChart.destroy();

    let totalAssistanceAmount = 0;
    $.ajax({
        url: 'controllers/dashboardController.php',
        cache: false,
        dataType: 'json',
        type: 'POST',
        data: { trans: "total_approved", span: $span, tk: $token },
        success: function(result) {	
            $('#approved-assistance-card').html(`${result['data']['total']}`);

            if(percentTotalAssistanceAmountChart != null && $timer)
            percentTotalAssistanceAmountChart.destroy();

            totalAssistanceAmount = result['data']['amount'] ?? 0;
            percentTotalAssistanceAmountChart = new Chart(percentTotalAssistanceAmount, {
                type: 'doughnut',
                data: {
                    labels: ['Total Amount'],
                    datasets: [{
                        data: [totalAssistanceAmount],
                        backgroundColor: [
                        'rgba(43, 122, 120, .8)',
                        ],
                    }]
                },
                options: {
                    cutout: '90%',
                    plugins: {
                        legend: {
                            display: false
                        },
                        counter: {
                            fontSize: (calculateFontSize('₱ ' + totalAssistanceAmount)) + 'px',
                            fontFamily: 'sans-serif',
                            dataValue: '₱ ' + numberWithCommas(totalAssistanceAmount),
                            dataTitle: 'Total Amount',
                        }
                    },
                    maintainAspectRatio: false,
                },
                plugins: [counter]
            });
        },
        error: function(err){
            console.log("Failed to load statistics.");
            console.log(err);
        }
    });
}

let totalAssistance = ($span,$token,$timer=false) => {
    if(percentTotalOverrideAmountChart != null && !$timer)
        percentTotalOverrideAmountChart.destroy();

    let totalOverrideAmount = 0;
    $.ajax({
        url: 'controllers/dashboardController.php',
        cache: false,
        dataType: 'json',
        type: 'POST',
        data: { trans: "total_override", span: $span, tk: $token },
        success: function(result) {	
            $('#override-assistance-card').html(`${result['data']['total']}`);

            if(percentTotalOverrideAmountChart != null && $timer)
            percentTotalOverrideAmountChart.destroy();

            totalOverrideAmount = result['data']['amount'] ?? 0;
            percentTotalOverrideAmountChart = new Chart(percentTotalOverrideAmount, {
                type: 'doughnut',
                data: {
                    labels: ['Total Amount'],
                    datasets: [{
                        data: [totalOverrideAmount],
                        backgroundColor: [
                        'rgba(43, 122, 120, .8)',
                        ],
                    }]
                },
                options: {
                    cutout: '90%',
                    plugins: {
                        legend: {
                            display: false
                        },
                        counter: {
                            fontSize: (calculateFontSize('₱ ' + totalOverrideAmount)) + 'px',
                            fontFamily: 'sans-serif',
                            dataValue: '₱ ' + numberWithCommas(totalOverrideAmount),
                            dataTitle: 'Total Amount',
                        }
                    },
                    maintainAspectRatio: false,
                },
                plugins: [counter]
            });
        },
        error: function(err){
            console.log("Failed to load statistics.");
            console.log(err);
        }
    });
}

let totalAssistanceAmountCategory = ($span,$token,$timer=false) => {
    if(percentTotalAssistanceAmountCategoryChart != null && !$timer)
    percentTotalAssistanceAmountCategoryChart.destroy();

    $.ajax({
        url: 'controllers/dashboardController.php',
        cache: false,
        dataType: 'json',
        type: 'POST',
        data: { trans: "total_approved_amount_by_category", span: $span, tk: $token },
        success: function(result) {	
            let category = [];
            let values = [];
            for(let i = 0; i<result['data'].length; i++)
            {
                category.push(result['data'][i]['category']);
                values.push(result['data'][i]['amount']);
            }

            if(percentTotalAssistanceAmountCategoryChart != null && $timer)
            percentTotalAssistanceAmountCategoryChart.destroy();

            percentTotalAssistanceAmountCategoryChart = new Chart(percentTotalAssistanceAmountCategory, {
                type: 'pie',
                data: {
                    labels: category,
                    datasets: [{
                        label: 'New',
                        data: values,
                        backgroundColor: [
                            'rgba(43, 122, 120, .8)',
                            'rgba(58, 175, 169, .8)',
                        ],
                    }]
                },
                options: {
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    maintainAspectRatio: false,
                }
            });
        },
        error: function(err){
            console.log("Failed to load statistics.");
            console.log(err);
        }
    });
}

let remainingBalance = ($token,$timer = false) => {
    if(percentRemainingBalanceChart != null && !$timer)
    percentRemainingBalanceChart.destroy();

    let totalRemainingBalance = 0;
    $.ajax({
        url: 'controllers/dashboardController.php',
        cache: false,
        dataType: 'json',
        type: 'POST',
        data: { trans: "total_remaining_balance", tk: $token },
        success: function(result) {	
            totalRemainingBalance = result['data']['amount'] ?? 0;
            let criticalBalance = result['data']['critical'] ?? 0;
            let lastReplenishBalance = result['data']['total'] ?? 0;
            let spent = lastReplenishBalance - totalRemainingBalance;
            let remainingColor = (parseFloat(totalRemainingBalance) > parseFloat(criticalBalance)) ? 'rgba(43, 122, 120, 0.8)' : 'rgba(255, 0, 0, 0.8)';

            if(percentRemainingBalanceChart != null && $timer)
                percentRemainingBalanceChart.destroy();

            percentRemainingBalanceChart = new Chart(percentRemainingBalance, {
                type: 'doughnut',
                data: {
                    labels: ['Total Approved','Remaining Balance'],
                    datasets: [{
                        data: [spent,totalRemainingBalance],
                        backgroundColor: [
                            'rgba(192, 192, 192, 0.8)',
                            remainingColor,
                        ],
                    }]
                },
                options: {
                    cutout: '90%',
                    plugins: {
                        legend: {
                            display: false
                        },
                        counter: {
                            fontSize: (calculateFontSize('₱ ' + totalRemainingBalance)) + 'px',
                            fontFamily: 'sans-serif',
                            dataValue: '₱ ' + numberWithCommas(totalRemainingBalance),
                            dataTitle: 'Remaining Balance',
                        }
                    },
                    maintainAspectRatio: false,
                },
                plugins: [counter]
            });
        },
        error: function(err){
            console.log("Failed to load statistics.");
            console.log(err);
        }
    });
}

let totalAssistanceBarangay = ($span,$token,$timer=false) => {
    if(percentTotalAssistanceBarangayChart != null && !$timer)
        percentTotalAssistanceBarangayChart.destroy();

    $.ajax({
        url: 'controllers/dashboardController.php',
        cache: false,
        dataType: 'json',
        type: 'POST',
        data: { trans: "total_approved_by_barangay", span: $span, tk: $token },
        success: function(result) {	
            let barangay = [];
            let values = [];
            for(let i = 0; i<result['data'].length; i++)
            {
                barangay.push(result['data'][i]['barangay']);
                values.push(result['data'][i]['total']);
            }
            if(percentTotalAssistanceBarangayChart != null && $timer)
                percentTotalAssistanceBarangayChart.destroy();

            percentTotalAssistanceBarangayChart = new Chart(percentTotalAssistanceBarangay, {
                type: 'bar',
                data: {
                    labels: barangay,
                    datasets: [{
                        label: 'Total',
                        data: values,
                        backgroundColor: [
                            'rgba(43, 122, 120, .8)',
                        ],
                    }]
                },
                options: {
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        x: {
                          grid: {
                            display: false
                          },
                        },
                        y: {
                          grid: {
                            display: false
                          }
                        }
                    },
                    maintainAspectRatio: false,
                }
            });
        },
        error: function(err){
            console.log("Failed to load statistics.");
            console.log(err);
        }
    });
}

let totalAssistanceGender = ($span,$token,$timer=false) => {
    if(percentTotalAssistanceGenderChart != null && !$timer)
    percentTotalAssistanceGenderChart.destroy();

    $.ajax({
        url: 'controllers/dashboardController.php',
        cache: false,
        dataType: 'json',
        type: 'POST',
        data: { trans: "total_approved_by_gender", span: $span, tk: $token },
        success: function(result) {	
            let gender = [];
            let values = [];
            for(let i = 0; i<result['data'].length; i++)
            {
                gender.push(result['data'][i]['sex']);
                values.push(result['data'][i]['total']);
            }

            if(percentTotalAssistanceGenderChart != null && $timer)
            percentTotalAssistanceGenderChart.destroy();

            percentTotalAssistanceGenderChart = new Chart(percentTotalAssistanceGender, {
                type: 'pie',
                data: {
                    labels: gender,
                    datasets: [{
                        label: 'New',
                        data: values,
                        backgroundColor: [
                            'rgba(43, 122, 120, .8)',
                            'rgba(58, 175, 169, .8)',
                        ],
                    }]
                },
                options: {
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    maintainAspectRatio: false,
                }
            });
        },
        error: function(err){
            console.log("Failed to load statistics.");
            console.log(err);
        }
    });
}

let totalAssistancePlatform = ($span,$token,$timer=false) => {
    if(percentTotalAssistancePlatformChart != null && !$timer)
    percentTotalAssistancePlatformChart.destroy();

    $.ajax({
        url: 'controllers/dashboardController.php',
        cache: false,
        dataType: 'json',
        type: 'POST',
        data: { trans: "total_approved_by_platform", span: $span, tk: $token },
        success: function(result) {	
            let platform = [];
            let values = [];
            for(let i = 0; i<result['data'].length; i++)
            {
                platform.push(result['data'][i]['typeClient']);
                values.push(result['data'][i]['total']);
            }

            if(percentTotalAssistancePlatformChart != null && $timer)
            percentTotalAssistancePlatformChart.destroy();

            percentTotalAssistancePlatformChart = new Chart(percentTotalAssistancePlatform, {
                type: 'pie',
                data: {
                    labels: platform,
                    datasets: [{
                        label: 'New',
                        data: values,
                        backgroundColor: [
                            'rgba(43, 122, 120, .8)',
                            'rgba(58, 175, 169, .8)',
                        ],
                    }]
                },
                options: {
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    maintainAspectRatio: false,
                }
            });
        },
        error: function(err){
            console.log("Failed to load statistics.");
            console.log(err);
        }
    });
}

let totalAssistanceAge = ($span,$token,$timer=false) => {
    if(percentTotalAssistanceAgeChart != null && !$timer)
        percentTotalAssistanceAgeChart.destroy();

    $.ajax({
        url: 'controllers/dashboardController.php',
        cache: false,
        dataType: 'json',
        type: 'POST',
        data: { trans: "total_approved_by_age", span: $span, tk: $token },
        success: function(result) {	
            let age = [
                '0-10',
                '11-20',
                '21-30',
                '31-40',
                '41-50',
                '51-60',
                '61-70',
                '71-80',
                '81-90',
                '90+',
            ];
            let values = [
                0,
                0,
                0,
                0,
                0,
                0,
                0,
                0,
                0,
                0,
            ];
            for(let i = 0; i<result['data'].length; i++)
            {
                if(result['data'][i]['age'] > 90)
                    values[9] += parseInt(result['data'][i]['total']??0);
                else if(result['data'][i]['age'] > 80)
                    values[8] += parseInt(result['data'][i]['total']??0);
                else if(result['data'][i]['age'] > 70)
                    values[7] += parseInt(result['data'][i]['total']??0);
                else if(result['data'][i]['age'] > 60)
                    values[6] += parseInt(result['data'][i]['total']??0);
                else if(result['data'][i]['age'] > 50)
                    values[5] += parseInt(result['data'][i]['total']??0);
                else if(result['data'][i]['age'] > 40)
                    values[4] += parseInt(result['data'][i]['total']??0);
                else if(result['data'][i]['age'] > 30)
                    values[3] += parseInt(result['data'][i]['total']??0);
                else if(result['data'][i]['age'] > 20)
                    values[2] += parseInt(result['data'][i]['total']??0);
                else if(result['data'][i]['age'] > 10)
                    values[1] += parseInt(result['data'][i]['total']??0);
                else
                    values[0] += parseInt(result['data'][i]['total']??0);
            }

            if(percentTotalAssistanceAgeChart != null && $timer)
            percentTotalAssistanceAgeChart.destroy();

            percentTotalAssistanceAgeChart = new Chart(percentTotalAssistanceAge, {
                type: 'bar',
                data: {
                    labels: age,
                    datasets: [{
                        label: 'Total',
                        data: values,
                        backgroundColor: [
                            'rgba(43, 122, 120, .8)',
                        ],
                    }]
                },
                options: {
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        x: {
                          grid: {
                            display: false
                          }
                        },
                        y: {
                          grid: {
                            display: false
                          }
                        }
                    },
                    maintainAspectRatio: false,
                }
            });
        },
        error: function(err){
            console.log("Failed to load statistics.");
            console.log(err);
        }
    });
}

let loginLogs = ($token) => {
    $.ajax({
        url: 'controllers/dashboardController.php',
        cache: false,
        dataType: 'json',
        type: 'POST',
        data: { trans: "login_logs", tk: $token },
        success: function(result) {	
            let onlineCount = 0;
            let rows = '';

            $.each(result['data'], function(index, value) {
                if(value.online_status == "Online") onlineCount++;
                rows += `
                    <div class="row g-1 my-1">
                        <div class="col ${(value.online_status == "Online") ? 'text-success' : 'text-secondary'}" style="display:block;line-height:1;">
                            <i class="fas fa-user-circle fa-2x"></i>
                        </div>
                        <div class="col-8" style="display:block;line-height:1;">
                            ${value.fullname}
                            <small class="text-monospace text-muted"><br/>${value.office}</small>
                        </div>
                        <div class="col-3 text-center" style="display:block;line-height:1;">
                            <span class="${(value.online_status == "Online") ? 'badge bg-success' : 'badge bg-secondary'}">
                                ${value.online_status??'Offline'}
                            </span>
                            <small><br/>${value.ip}</small>
                        </div>
                    </div>
                `;
            });

            $('#online-users-card').html(`${onlineCount}`);
            $('#login-logs-container').html(rows);
        },
        error: function(err){
            console.log("Failed to load statistics.");
            console.log(err);
        }
    });
}

let cashFlow = ($token) => {
    $.ajax({
        url: 'controllers/dashboardController.php',
        cache: false,
        dataType: 'json',
        type: 'POST',
        data: { trans: "cash_flow", tk: $token },
        success: function(result) {	
            let rows = '';

            $.each(result['data'], function(index, value) {
                rows += `
                    <div class="row g-1 mb-3">
                        <div class="col" style="display:block;line-height:1;">
                            ${value.date}  
                            <small class="text-monospace text-muted"><br/>${value.details}</small>
                            <small class="text-monospace text-muted"><br/><strong>${value.officer}</strong></small>
                        </div>
                        <div class="col-2 text-end" style="display:block;line-height:1;">
                            ₱ ${numberWithCommas(value.debit)}
                        </div>
                        <div class="col-2 text-end" style="display:block;line-height:1;">
                            ₱ ${numberWithCommas(value.credit)}
                        </div>
                        <div class="col-3 text-end" style="display:block;line-height:1;">
                            ₱ ${numberWithCommas(value.amount)}
                        </div>
                    </div>
                `;
            });

            $('#cash-flow-container').html(rows);
        },
        error: function(err){
            console.log("Failed to load statistics.");
            console.log(err);
        }
    });
}

let totalCancelled = ($span,$token) => {
    $.ajax({
        url: 'controllers/dashboardController.php',
        cache: false,
        dataType: 'json',
        type: 'POST',
        data: { trans: "total_cancelled", span: $span, tk: $token },
        success: function(result) {	
            $('#cancelled-total-card').html(`${result['data']['total']}`);
        },
        error: function(err){
            console.log("Failed to load statistics.");
            console.log(err);
        }
    });
}

function compareArrayObjects(list) {
    const rows = [];

    function compareObjects(oldObj, newObj, parentKey = '') {
        const changes = [];

        // Check if oldObj is an empty object
        if (Object.keys(oldObj).length === 0) {
            // Display all properties from newObj
            for (const key in newObj) {
                const newValue = newObj[key];
                const fullKey = parentKey ? `${parentKey}.${key}` : key;
                changes.push(`'${fullKey}': '${newValue}'`);
            }
        } else if (Object.keys(newObj).length === 0) {
            // Display all properties from oldObj
            for (const key in oldObj) {
                const oldValue = oldObj[key];
                const fullKey = parentKey ? `${parentKey}.${key}` : key;
                changes.push(`'${fullKey}': '${oldValue}'`);
            } 
        } else {
            for (const key in oldObj) {
                if (newObj.hasOwnProperty(key)) {
                    const oldValue = oldObj[key];
                    const newValue = newObj[key];
                    const fullKey = parentKey ? `${parentKey}.${key}` : key;

                    if (typeof oldValue === 'object' && typeof newValue === 'object') {
                        if (Array.isArray(oldValue) && Array.isArray(newValue)) {
                            const arrayChanges = compareArrays(oldValue, newValue);
                            if (arrayChanges.length > 0) {
                                changes.push(`'${fullKey}': [${arrayChanges}]`);
                            }
                        } else {
                            const objectChanges = compareObjects(oldValue, newValue, fullKey);
                            if (objectChanges.length > 0) {
                                changes.push(`'${fullKey}': { ${objectChanges} }`);
                            }
                        }
                    } else if (oldValue !== newValue) {
                        changes.push(`'${fullKey}': '${oldValue}' to '${newValue}'`);
                    }
                }
            }
        }

        return changes.join(', ');
    }

    function compareArrays(oldArray, newArray) {
        const arrayChanges = [];

        for (let i = 0; i < oldArray.length || i < newArray.length; i++) {
            const oldItem = oldArray[i];
            const newItem = newArray[i];

            if (typeof oldItem === 'object' && typeof newItem === 'object') {
                const objectChanges = compareObjects(oldItem, newItem);
                if (objectChanges.length > 0) {
                    arrayChanges.push(`{ ${objectChanges} }`);
                }
            } else if (oldItem !== newItem) {
                arrayChanges.push(`'${i}': '${oldItem}' to '${newItem}'`);
            }
        }

        return arrayChanges.join(', ');
    }

    $.each(list, function (index, item) {
        const olddumps = JSON.parse(item.olddumps);
        const newdumps = JSON.parse(item.newdumps);

        const changes = compareObjects(olddumps, newdumps);

        if (changes) {
            rows.push({
                'date': item.created_at,
                'transaction': item.actions,
                'history': `changes are ${changes}.`,
                'officer': item.officer
            });
        }
    });

    return rows;
}

let history = ($token) => {
    $.ajax({
        url: 'controllers/dashboardController.php',
        cache: false,
        dataType: 'json',
        type: 'POST',
        data: { trans: "history", tk: $token },
        success: function(result) {	
            console.log(result);
			let history = compareArrayObjects(result['data']);
			if(history.length > 0){
                let rows = '';

                $.each(history, function(index, value) {
                    rows += `
                        <tr class="border-top">
                            <td class="align-top text-nowrap" width="130px">
                                ${value.date}<br/>
                                <b>${value.transaction}</b>
                            </td>
                            <td class="align-top">${value.history}</td>
                            <td class="align-top text-nowrap pl-2">${value.officer}</td>
                        </tr>
                    `;
                });

                $('#history-container').html(rows);
            }
        },
        error: function(err){
            console.log("Failed to load statistics.");
            console.log(err);
        }
    });
}

let autorefreshstat = (token)=>{
    totalAssistance(_statspan,token,true);
    totalOverride(_statspan,token,true);
    totalCancelled(_statspan,token);
    loginLogs(token);
    totalAssistanceAmountCategory(_statspan,token,true);
    remainingBalance(token,true);
    totalAssistanceGender(_statspan,token,true);
    totalAssistancePlatform(_statspan,token,true);
    totalAssistanceAge(_statspan,token,true);
    totalAssistanceBarangay(_statspan,token,true);
    cashFlow(token);
    history(token);

    
    // Removed By: Teddy C. 09/15/2023 10:21.
    // Optimizing the use of timeouts.
    // setTimeout(function(){autorefreshstat(token);}, 60000);
    // End Teddy C.
}

function startload(token){
	_statspan = $('#statistic-span').val();
    _dashboardToken = token;

    autorefreshstat(token);

    $('#statistic-span').change(function(){
        _statspan = $(this).val();
        totalAssistance(_statspan,token);
        totalOverride(_statspan,token);
        totalCancelled(_statspan,token);
        loginLogs(token);
        totalAssistanceAmountCategory(_statspan,token);
        remainingBalance(token);
        totalAssistanceGender(_statspan,token);
        totalAssistancePlatform(_statspan,token);
        totalAssistanceAge(_statspan,token);
        totalAssistanceBarangay(_statspan,token);
        cashFlow(token);
        history(history);
    });
}

let calculateFontSize = function(x){
    let len = x.toString().length;
    if(len <= 7)
        return 30;
    else
        return (30 - ((len-7)/0.55));
}

let numberWithCommas = function(x) {
    return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}