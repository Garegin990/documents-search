<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Search</title>
    <script src="https://code.jquery.com/jquery-3.6.1.min.js" integrity="sha256-o88AwQnZB+VDvE9tvIXrMQaPlFFSUTR+nldQm1LuPXQ=" crossorigin="anonymous"></script>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>

<body class="container">
    <div class="row">
        <div class="col-6 offset-3 mt-5">
            <form class="row g-3" method="post" enctype="multipart/form-data">
                <div>
                    <label for="search" class="form-label">Search</label>
                    <input type="text" name="search" class="form-control @error('search') is-invalid @enderror" id="search">
                    <div class="field-search d-none"></div>
                </div>

                <div>
                    <label for="document" class="form-label">Document</label>
                    <input type="file" name="document" class="form-control @error('document') is-invalid @enderror" aria-label="file example" accept=".doc,.docx,.pdf" id="document">
                    <div class="field-document d-none"></div>
                </div>

                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="" id="with_whitespaces" name="with_whitespaces">
                    <label class="form-check-label" for="with_whitespaces">With whitespaces</label>
                    <div class="field-with_whitespaces d-none"></div>
                </div>

                <div>
                    <button class="btn btn-primary" type="submit">Submit</button>
                </div>
            </form>
        </div>
    </div>
    <div class="row d-none">
        <div class="col-10 offset-1 mt-5">
            <div class="h3 table-title">
                Search results for <span class="fw-bold"></span>
            </div>
            <table class="table docs-table">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Doc name</th>
                        <th scope="col">Searched content</th>
                        <th scope="col">Upload date</th>
                    </tr>
                </thead>
                <tbody>

                </tbody>
            </table>
        </div>

    </div>
    <script>
        window.onload = function () {
            $('form').on('submit', function(e) {
                e.preventDefault();

                $('[class*="field-"]').addClass('d-none')
                let data = new FormData;
                let inputText = $('[type="text"]').val()
                let withWhitespaces = $('#check_id').is(":checked")
                let document = $('[type="file"]').prop('files')[0]

                data.append('search', inputText)
                document         && data.append('document', document)
                withWhitespaces && data.append('with_whitespaces', withWhitespaces)

                $.ajax({
                    url: window.location.href,
                    method: 'post',
                    data,
                    cache: false,
                    contentType: false,
                    processData: false,
                    success: function({docs}) {
                        let htmlContent = ''
                        if (docs?.length) {
                            for (const doc of docs) {
                                doc.content = doc.content.length > 100 ? doc.content.slice(0, 100) + '...' : doc.content
                                htmlContent += `
                                <tr>
                                    <th scope="row">${doc.id}</th>
                                    <td>${doc.name}</td>
                                    <td>${doc.content}</td>
                                    <td>${doc.created_date}</td>
                                </tr>`
                            }
                        } else {
                            htmlContent = '<p>No data found</p>'
                        }

                        $('.docs-table tbody').html(htmlContent)
                        $('.table-title span').html(inputText)
                        $('.row.d-none').removeClass('d-none')
                    },
                    error: function({responseJSON: {errors}}) {
                        for (const field in errors) {
                            console.log(errors[field][0]);
                            console.log($(`.field-${field}`));
                            $(`.field-${field}`).html(errors[field][0]).removeClass('d-none')
                        }
                    }
                })
            })

        }
    </script>
</body>

</html>
