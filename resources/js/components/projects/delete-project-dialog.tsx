import { Delete02Icon } from '@hugeicons/core-free-icons';
import { HugeiconsIcon } from '@hugeicons/react';
import { Form } from '@inertiajs/react';
import { useState } from 'react';
import ProjectController from '@/actions/App/Http/Controllers/ProjectController';
import { CopyField } from '@/components/copy-field';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { Project } from '@/types';

export function DeleteProjectDialog({ project }: { project: Project }) {
    const [open, setOpen] = useState(false);
    const [confirmation, setConfirmation] = useState('');
    const isConfirmed = confirmation === project.name;

    function handleOpenChange(next: boolean) {
        setOpen(next);

        if (!next) {
            setConfirmation('');
        }
    }

    return (
        <Dialog open={open} onOpenChange={handleOpenChange}>
            <DialogTrigger asChild>
                <Button variant="destructive" data-test="delete-project-button">
                    <HugeiconsIcon icon={Delete02Icon} className="size-4" />
                    Delete project
                </Button>
            </DialogTrigger>
            <DialogContent>
                <DialogTitle>Delete project</DialogTitle>
                <DialogDescription>
                    This permanently deletes "{project.name}" and all of its
                    webhook endpoints, events, and API keys. This cannot be
                    undone.
                </DialogDescription>

                <div className="space-y-4">
                    <div className="grid gap-2">
                        <Label>Project name</Label>
                        <CopyField value={project.name} />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="delete_confirmation">
                            Type the project name to confirm
                        </Label>
                        <Input
                            id="delete_confirmation"
                            value={confirmation}
                            onChange={(e) => setConfirmation(e.target.value)}
                            autoComplete="off"
                            autoFocus
                            data-test="delete-project-confirmation-input"
                        />
                    </div>
                </div>

                <Form
                    {...ProjectController.destroy.form(project)}
                    options={{ preserveScroll: true }}
                >
                    {({ processing }) => (
                        <DialogFooter className="gap-2">
                            <DialogClose asChild>
                                <Button variant="secondary">Cancel</Button>
                            </DialogClose>
                            <Button
                                variant="destructive"
                                disabled={processing || !isConfirmed}
                                data-test="confirm-delete-project-button"
                            >
                                Delete project
                            </Button>
                        </DialogFooter>
                    )}
                </Form>
            </DialogContent>
        </Dialog>
    );
}
