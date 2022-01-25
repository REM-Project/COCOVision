function showClock() {
      let nowTime = new Date();
      let year = nowTime.getFullYear();
      let month = nowTime.getMonth()+1;
      let day = nowTime.getDate();
      let hour = nowTime.getHours();
      hour = set0(hour)
      let min  = nowTime.getMinutes();
      min = set0(min)
      let sec  = nowTime.getSeconds();
      sec = set0(sec)
      let msg = year + "/" + month + "/" + day +"　" + hour + ":" + min  ;
      document.getElementById("realtime").innerHTML = msg;
}

function set0 (time) {
	let plusnum = 0;
	if(time < 10) {
		plusnum = "0" + time;
		return plusnum;
	} else {
		return time
	}
}

window.onload = showClock;
setInterval(showClock, 1000);