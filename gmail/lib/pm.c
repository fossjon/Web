//gcc -Wall -O2 -o pm pm.c -I ./gmp-*/ -L ./gmp-*/.libs/ -lgmp

#include "gmp.h"

int main(int argc, char **argv)
{
	if (argc != 4) { return 0; }
	
	mpz_t b, e, m, r;
	
	mpz_init(b);
	mpz_init(e);
	mpz_init(m);
	mpz_init(r);
	
	mpz_set_str(b, argv[1], 10);
	mpz_set_str(e, argv[2], 10);
	mpz_set_str(m, argv[3], 10);
	mpz_powm(r, b, e, m);
	
	gmp_printf("%Zd\n", r);
	
	mpz_clear(b);
	mpz_clear(e);
	mpz_clear(m);
	mpz_clear(r);
	
	return 0;
}
