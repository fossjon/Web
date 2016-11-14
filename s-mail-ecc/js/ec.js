var cnst = new BigInteger("486662", 10);
var prime = new BigInteger("57896044618658097711785492504343953926634992332820282019728792003956564819949", 10);
var two = new BigInteger("2", 10);
var three = new BigInteger("3", 10);

function safe_sub(a, p)
{
	if (a.compareTo(BigInteger.ZERO) < 0)
	{
		var r = a.abs().divide(p);
		a = a.add(r.multiply(p));
		if (a.compareTo(BigInteger.ZERO) < 0)
		{
			a = a.add(p);
		}
	}
	return a;
}

function point_add(q, r, p)
{
	var ys = safe_sub(q[1].subtract(r[1]), p);
	var xs = safe_sub(q[0].subtract(r[0]), p);
	var s = ys.multiply(xs.modInverse(p)).mod(p);
	var rx = s.modPow(two, p);
	rx = safe_sub(rx.subtract(q[0]), p);
	rx = safe_sub(rx.subtract(r[0]), p);
	var rp = safe_sub(q[0].subtract(rx), p);
	var ry = safe_sub(s.multiply(rp).mod(p).subtract(q[1]), p);
	return [rx, ry];
}

function point_dub(a, q, p)
{
	var ttt = three.multiply(q[0].modPow(two, p)).mod(p);
	var tt = two.multiply(q[1]).mod(p);
	var s = ttt.add(a).multiply(tt.modInverse(p)).mod(p);
	tt = two.multiply(q[0]).mod(p);
	var rx = safe_sub(s.modPow(two, p).subtract(tt), p);
	var rp = safe_sub(q[0].subtract(rx), p);
	var ry = safe_sub(s.multiply(rp).mod(p).subtract(q[1]), p);
	return [rx, ry];
}

function point_mul(n, q, p)
{
	//if(this.isInfinity()) return this;
	//if(k.signum() == 0) return this.curve.getInfinity();
	
	var e = n;
	var h = e.multiply(three);
	
	var neg = [q[0], safe_sub(q[1].negate(), p)];
	var R = q;
	
	var i;
	for (i = (h.bitLength() - 2); i > 0; --i)
	{
		R = point_dub(BigInteger.ONE, R, p);
		
		var hBit = h.testBit(i);
		var eBit = e.testBit(i);
		
		if (hBit != eBit)
		{
			R = point_add(hBit ? q : neg, R, p);
		}
	}
	
	return R;
}

function ord_r(r, n)
{
	var k = three;
	while (1)
	{
		if (n.modPow(k, r).equals(BigInteger.ONE))
		{
			return k;
		}
		k = k.add(BigInteger.ONE);
	}
}

function ifrexp(x)
{
	var e = BigInteger.ZERO;
	while (x.mod(two).equals(BigInteger.ZERO))
	{
		x = x.divide(two);
		e = e.add(BigInteger.ONE);
	}
	return [x, e];
}

function tonelli(a, p)
{
	var pmo = p.subtract(BigInteger.ONE);
	if (a.modPow(pmo.divide(two), p).equals(pmo))
	{
		return -1;
	}
	var t = ifrexp(pmo);
	var s = t[0], e = t[1];
	var n = two;
	while (n.compareTo(p) < 0)
	{
		if (n.modPow(pmo.divide(two), p).equals(pmo))
		{
			break;
		}
		n = n.add(BigInteger.ONE);
	}
	var x = a.modPow(s.add(BigInteger.ONE).divide(two), p);
	var b = a.modPow(s, p);
	var g = n.modPow(s, p);
	var r = e;
	while (1)
	{
		var m = BigInteger.ZERO;
		while (m.compareTo(r) < 0)
		{
			if (ord_r(p, b).equals(two.pow(m)))
			{
				break;
			}
			if (m.add(BigInteger.ONE).equals(r))
			{
				break;
			}
			m = m.add(BigInteger.ONE);
		}
		if (m.equals(BigInteger.ZERO))
		{
			return x;
		}
		var pmi = two.pow(r.subtract(m).subtract(BigInteger.ONE));
		x = x.multiply(g.modPow(pmi, p)).mod(p);
		pmi = two.pow(r.subtract(m));
		g = g.modPow(pmi, p);
		b = b.multiply(g).mod(p);
		if (b.equals(BigInteger.ONE))
		{
			return x;
		}
		r = m;
	}
	return -1;
}

function curve_25519(x, c, p)
{
	var y2 = x.modPow(three, p).add(c.multiply(x.modPow(two, p))).add(x).mod(p);
	return tonelli(y2, p);
}

function form_pub(pubstr)
{
	var tmplst = pubstr.split("\n\n");
	if (tmplst.length < 2)
	{
		tmplst = ["", ""];
	}
	tmplst[0] = tmplst[0].split("\n");
	tmplst[1] = tmplst[1].split("\n");
	if ((tmplst[0].length < 2) || (tmplst[1].length < 2))
	{
		tmplst[0] = ["2", "3"];
		tmplst[1] = ["3", "4"];
	}
	tmplst[0] = [new BigInteger(tmplst[0][0], 10), new BigInteger(tmplst[0][1], 10)];
	tmplst[1] = [new BigInteger(tmplst[1][0], 10), new BigInteger(tmplst[1][1], 10)];
	return tmplst;
}

function pub_enc(pnt, aG, msg)
{
	var l = prime.toString(10).length, b = "";
	for (var x = 0; x < l; ++x)
	{
		b += Math.floor(Math.random() * 10).toString();
	}
	var pritmp = new BigInteger(b, 10);
	
	pritmp = pritmp.mod(prime);
	while (pritmp.compareTo(three) < 0)
	{
		pritmp = pritmp.add(BigInteger.ONE);
	}
	
	var bG = point_mul(pritmp, pnt, prime);
	var baG = point_mul(pritmp, aG, prime);
	
	var mbaG = [msg[0].multiply(baG[0]), msg[1].multiply(baG[1])];
	var outp = (bG[0].toString(10) + "\n" + bG[1].toString(10) + "\n\n" + mbaG[0].toString(10) + "\n" + mbaG[1].toString(10));
	
	return window.btoa(outp);
}

function pri_dec(bG, mbaG, pkey)
{
	var abG = point_mul(pkey, bG, prime);
	var keya = mbaG[0].divide(abG[0]).toString(16);
	var keyb = mbaG[1].divide(abG[1]).toString(16);
	while (keya.length < 32) { keya = ("0" + keya); }
	while (keyb.length < 32) { keyb = ("0" + keyb); }
	return (keya + keyb).hexTOstr();
}
