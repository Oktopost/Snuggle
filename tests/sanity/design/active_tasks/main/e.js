function sleep(delayMS)
{
    var start = new Date().getTime();
    while (new Date().getTime() < start + delayMS);
}

function (doc)
{
	sleep(5000);
	emit(1);
}