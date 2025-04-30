<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Financial Year Holidays</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <div class="container mt-5">
        <h2 class="mb-4">Financial Year Holiday Checker</h2>

        <div class="row mb-3">
            <div class="col-md-4">
                <label for="country" class="form-label">Country</label>
                <select id="country" class="form-select" onchange="getYears()">
                    <option value="uk">UK</option>
                    <option value="ireland">Ireland</option>
                </select>
            </div>

            <div class="col-md-4">
                <label for="year" class="form-label">Year</label>
                <select id="year" class="form-select"></select>
            </div>

            <div class="col-md-4 d-flex align-items-end">
                <button class="btn btn-success w-100" onclick="getHolidays()">Submit</button>
            </div>
        </div>

        <div id="holidays-div" class="mt-4"></div>
    </div>

    <script>
        const yearSelect = document.getElementById('year');
        const countrySelect = document.getElementById('country');

        function getYears() {
            const currentYear = new Date().getFullYear();
            const startYear = currentYear - 10;
            const country = countrySelect.value;
            yearSelect.innerHTML = '';

            for (let y = startYear; y <= currentYear; y++) {
                const option = document.createElement('option');
                if (country === 'uk') {
                    option.value = `${y}-${(y+1).toString().slice(2)}`;
                    option.text = `${y}-${(y+1).toString().slice(2)}`;
                } else {
                    option.value = y;
                    option.text = y;
                }
                yearSelect.appendChild(option);
            }
        }

        async function getHolidays() {
            const country = countrySelect.value;
            const year = yearSelect.value;

            try {
                const res = await axios.post('/get-holidays', {
                    country: country,
                    year: year
                });

                const data = res.data;
                let html = '<ul class="list-group mt-3">';
                html += `<li>${data.financial_year_start}</li><li>${data.financial_year_end}</li>`
                html += `<ul class="list-group mt-3">`;
                data.holidays.forEach(h => {
                    html += `<li class="list-group-item">${h.date} — ${h.name}</li>`;
                });
                html += `</ul>`;

                document.getElementById('holidays-div').innerHTML = html;
            } catch (error) {
                console.error(error);
            }
        }

        getYears();
    </script>
</body>
</html>
