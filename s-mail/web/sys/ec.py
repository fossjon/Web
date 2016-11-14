import random
import time

cnst = 486662
prime = (pow(2, 255) - 19)

def egcd(a, b):
	if (a == 0):
		return (b, 0, 1)
	else:
		(g, y, x) = egcd(b % a, a)
		return (g, x - (b / a) * y, y)

def inv_mod(a, m):
	(g, x, y) = egcd(a, m)
	if (g != 1):
		return -1
	else:
		return (x % m)

def pow_mod(b, e, m):
	r = 1
	b = (b % m)
	while (e > 0):
		if ((e % 2) == 1):
			r = ((r * b) % m)
		e = (e / 2)
		b = ((b * b) % m)
	return r

def safe_sub(a, p):
	if (a < 0):
		a += ((abs(a) / p) * p)
		if (a < 0):
			a += p
	return a

def point_add(q, r, p):
	ys = safe_sub(q[1] - r[1], p)
	xs = safe_sub(q[0] - r[0], p)
	s = ((ys * inv_mod(xs, p)) % p)
	rx = pow_mod(s, 2, p)
	rx = safe_sub(rx - q[0], p)
	rx = safe_sub(rx - r[0], p)
	rp = safe_sub(q[0] - rx, p)
	ry = safe_sub(((s * rp) % p) - q[1], p)
	return [rx, ry]

def point_dub(a, q, p):
	ttt = ((3 * pow_mod(q[0], 2, p)) % p)
	tt = ((2 * q[1]) % p)
	s = (((ttt + a) * inv_mod(tt, p)) % p)
	tt = ((2 * q[0]) % p)
	rx = safe_sub(pow_mod(s, 2, p) - tt, p)
	rp = safe_sub(q[0] - rx, p)
	ry = safe_sub(((s * rp) % p) - q[1], p)
	return [rx, ry]

def zpoint_mul(n, q, p):
	r = q; m = 1
	h = [[m, r]]; l = 1
	while (m < n):
		t = (m * 2)
		if (t <= n):
			r = point_dub(1, r, p); m = t
			h.append([m, r]); l += 1
		else:
			x = (l - 1)
			while (x > -1):
				while (m < n):
					t = (m + h[x][0])
					if (t <= n):
						r = point_add(h[x][1], r, p); m = t
					else:
						break
				x -= 1
	return r

def bitleng(a):
	b = 0
	while (a > 0):
		a = (a / 2)
		b += 1
	return b

def testbit(a, b):
	if ((a & pow(2, b)) > 0):
		return 1
	return 0

def point_mul(n, q, p):
	#if(this.isInfinity()) return this;
	#if(k.signum() == 0) return this.curve.getInfinity();
	
	e = n
	h = (e * 3)
	
	neg = [q[0], safe_sub(-1 * q[1], p)]
	R = q
	
	i = (bitleng(h) - 2)
	while (i > 0):
		R = point_dub(1, R, p)
		
		hBit = testbit(h, i)
		eBit = testbit(e, i)
		
		if (hBit != eBit):
			if (hBit):
				R = point_add(q, R, p)
			else:
				R = point_add(neg, R, p)
		
		i -= 1
	
	return R

def ord_r(r, n):
	k = 3
	while (1):
		if (pow_mod(n, k, r) == 1):
			return k
		k += 1

def ifrexp(x):
	e = 0
	while ((x % 2) == 0):
		x /= 2
		e += 1
	return (x, e)

def tonelli(a, p):
	if (pow_mod(a, (p - 1) / 2, p) == (p - 1)):
		raise ValueError("no sqrt possible")
	(s, e) = ifrexp(p - 1)
	n = 2
	while (n < p):
		if (pow_mod(n, (p - 1) / 2, p) == (p - 1)):
			break
		n += 1
	x = pow_mod(a, (s + 1) / 2, p)
	b = pow_mod(a, s, p)
	g = pow_mod(n, s, p)
	r = e
	while (1):
		m = 0
		while (m < r):
			if (ord_r(p, b) == pow(2, m)):
				break
			if ((m + 1) == r):
				break
			m += 1
		if (m == 0):
			return x
		x = ((x * pow_mod(g, pow(2, (r - m - 1)), p)) % p)
		g = pow_mod(g, pow(2, (r - m)), p)
		b = ((b * g) % p)
		if (b == 1):
			return x
		r = m
	return -1

def curve_25519(x, c, p):
	y2 = ((pow_mod(x, 3, p) + (c * pow_mod(x, 2, p)) + x) % p)
	return tonelli(y2, p)

def pub_enc(pnt, aG, msg):
	global cnst, prime
	
	x = 0; l = len(str(prime)); b = ""
	while (x < l):
		b += str(random.randint(0, 9))
		x += 1
	
	b = (int(b) % prime)
	while (b < 3):
		b += 1
	
	bG = point_mul(b, pnt, prime)
	baG = point_mul(b, aG, prime)
	
	mbaG = [msg[0] * baG[0], msg[1] * baG[1]]
	
	return [bG, mbaG]
