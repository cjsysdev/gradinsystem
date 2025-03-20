<?php $this->load->view('header') ?>

<script src="<?= base_url("/assets/jquery-3.5.1.slim.min.js") ?> "></script>
<script src="<?= base_url("/assets/underscore-min.js") ?> "></script>
<script src="<?= base_url("/assets/moment.min.js") ?> "></script>
<script src="<?= base_url("/assets/clndr.min.js") ?> "></script>

<style>
    #calendar {
        max-width: 800px;
        margin: 0 auto;
    }

    .day.event {
        background-color: #ffe0b2;
    }

    .day.today {
        background-color: #e0f7fa;
    }

    .day.adjacent-month {
        color: #ccc;
    }

    .clndr-previous-button,
    .clndr-next-button {
        cursor: pointer;
    }

    /* Ensure consistent sizing and alignment */
    .days-of-the-week,
    .days {
        display: flex;
        /* Use flexbox for consistent column widths */
        flex-wrap: wrap;
    }

    .days-of-the-week .col,
    .days .col {
        flex: 1 0 calc(100% / 7 - 4px);
        /* Equal width for 7 columns, accounting for margin */
        max-width: calc(100% / 7 - 4px);
        /* Prevent overflow */
        padding: 10px;
        text-align: center;
        border: 1px solid #ddd;
        border-radius: 5px;
        margin: 2px;
        box-sizing: border-box;
        /* Include padding/border in width calculation */
        min-height: 40px;
        /* Ensure day cells have enough height */
    }

    .days-of-the-week .col {
        font-weight: bold;
        border: none;
        /* No border for day names */
        background-color: #f8f9fa;
        /* Light background for clarity */
    }
</style>

<div class="container mt-4">
    <div id="calendar"></div>
</div>

<script id="calendar-template" type="text/template">
    <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center p-3">
                <div class="clndr-previous-button btn btn-sm btn-outline-secondary"><</div>
                <div class="month h4 mb-0 text-center flex-grow-1"><%= month %> <%= year %></div>
                <div class="clndr-next-button btn btn-sm btn-outline-secondary">></div>
            </div>
            <div class="card-body p-3">
                <div class="days-of-the-week mb-3">
                    <% _.each(daysOfTheWeek, function(day) { %>
                        <div class="col"><%= day %></div>
                    <% }); %>
                </div>
                <div class="days">
                    <% _.each(days, function(day) { %>
                        <div class="col day <%= day.classes %>"><%= day.day %></div>
                    <% }); %>
                </div>
            </div>
        </div>
    </script>

<script type="text/javascript">
    $(document).ready(function() {
        // Set week to start on Sunday (0) or Monday (1) as desired
        moment.locale('en', {
            week: {
                dow: 0 // Sunday start
            }
        });

        var events = [{
                'class_code': 'CC105',
                'class_name': 'INFORMATION MANAGEMENT',
                'type': 'LAB',
                'lastname': 'CEPADA',
                'firstname': 'CHERYLYN',
                'date': '2025-03-07 08:38:09'
            },
            {
                'class_code': 'CC103',
                'class_name': 'COMPUTER PROGRAMMING 2',
                'type': 'LAB',
                'lastname': 'CEPADA',
                'firstname': 'CHERYLYN',
                'date': '2025-03-06 07:50:21'
            }
        ];

        var formattedEvents = events.map(function(event) {
            return {
                date: moment(event.date).format('YYYY-MM-DD'),
                title: event.class_name + ' (' + event.type + ')',
                description: 'Instructor: ' + event.firstname + ' ' + event.lastname
            };
        });

        // Explicitly define days of the week
        var daysOfTheWeek = ['S', 'M', 'T', 'W', 'T', 'F', 'S'];

        $('#calendar').clndr({
            template: $('#calendar-template').html(),
            events: formattedEvents,
            daysOfTheWeek: daysOfTheWeek,
            clickEvents: {
                click: function(target) {
                    if (target.events.length) {
                        var eventsHtml = '';
                        target.events.forEach(function(event) {
                            eventsHtml += '<div><strong>' + event.title + '</strong><br>' + event.description + '</div>';
                        });
                        alert(eventsHtml);
                    }
                }
            },
            adjacentDaysChangeMonth: true,
            forceSixRows: true,
            startWithMonth: moment('2025-03-01')
        });
    });
</script>