// 循环链表解决 约瑟夫环问题
#include <stdio.h>
#include <stdlib.h>

typedef struct node
{
	int data;
	struct node *next;
}node;

// 创建循环链表
node *create(int n)
{
	node *p = NULL, *head;
	head = (node*)malloc(sizeof (node) );
	p = head;
	node *s;
	int i = 1;
	
	if ( 0 != n )
	{
		while ( i <= n )
		{
			s = (node *)malloc(sizeof (node) );
			s->data = i++;
			p->next = s;
			p = s;
		}
		s->next = head->next;
	}
	free(head);
	
	return s->next;
}

int main()
{
	int n = 41;
	int m =3;
	int i;
	node *p = create(n);
	node *temp;
	
	m %= n;
	printf("自杀身亡编号依次为：");
	while (p != p->next) 
	{
		for (i = 1; i < m-1; i++) 
		{
			p = p->next;
		}
		printf("%d->", p->next->data);
		temp = p->next;
		p->next = temp->next;
		free(temp);
		p = p->next;
	}
	printf("%d\n", p->data);
	
	return 0;
}