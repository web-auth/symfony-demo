function handlePublicKeyRequestOptions(data, successCallback, failureCallback) {
    fetch('/api/login/options', {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data),
    })
        .then(response => response.json())
        .then(json => successCallback(json))
        .catch(err => failureCallback(err));
}

function handlePublicKeyRequestResult(data, successCallback, failureCallback) {
    fetch('/api/login', {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data),
    })
        .then(response => response.json())
        .then(json => {
          console.log(json)
          return successCallback(json)
        })
        .catch(err => failureCallback(err));
}

export {
    handlePublicKeyRequestOptions, handlePublicKeyRequestResult,
};
