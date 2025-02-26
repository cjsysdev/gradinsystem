#include <stdio.h>

float celcius(float temperature){
	return (9.0/5.0 * temperature) + 32;
}

float fahrenheit(float temperature){
	return 	(temperature - 32) * 5.0/9.0;
}

int main() {
	float temperature;
	char unit, padayon;
	
	do {
		printf("Input Temperature: ");
		scanf("%f %c", &temperature, &unit);

		if(unit == 'c' || unit == 'C') {
			printf("%.2f F \n", celcius(temperature));
		} else {
			printf("%.2f C \n", fahrenheit(temperature));
		}
		printf("Do you want to continue \n");
		scanf(" %c", &padayon);
	}	while(padayon == 'y') ;

}