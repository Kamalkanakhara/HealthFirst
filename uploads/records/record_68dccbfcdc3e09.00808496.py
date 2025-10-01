num= int(input("Enter a number: "))

num1=str(num)
num2=len(num1)

total=sum(int(digit)**num2 for digit in num1)
if total==num:
    print("Is a Armstrong NUmber")
else:
    print("Not a armstrong number")