#!/bin/sh
# https://developer.typeform.com/create/reference/create-form
curl \
	--request GET \
	--url https://api.typeform.com/forms/N2BwhIXs \
	--header 'Authorization: Bearer __token__' \


# https://developer.typeform.com/responses/reference/retrieve-responses
curl \
	--request GET \
	--url https://api.typeform.com/forms/N2BwhIXs/responses \
	--header 'Authorization: Bearer __token__' \

